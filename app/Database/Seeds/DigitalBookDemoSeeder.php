<?php

namespace App\Database\Seeds;

use App\Models\BaseUuidModel;
use CodeIgniter\Database\Seeder;

class DigitalBookDemoSeeder extends Seeder
{
    private const DEMO_USER_EMAIL = 'demo.reader@example.test';
    private const DEMO_USER_PASSWORD = 'DemoReader123!';
    private const DEMO_USER_NAME = 'Demo Reader';
    private const DEMO_AUTHOR_MARKER = 'DEMO-DBR';
    private const DEMO_ORDER_NO = 'DEMO-DBR-ORDER-001';

    public function run()
    {
        if (! $this->requiredTablesExist()) {
            echo "DigitalBookDemoSeeder: gerekli tablolar eksik, islem durduruldu.\n";
            return;
        }

        $user = $this->upsertDemoUser();
        $products = $this->upsertDemoProducts();
        $this->upsertDigitalContents($products);
        $order = $this->upsertDemoOrder($user, $products);
        $this->upsertDemoOrderItems($order['id'], $products);

        echo "DigitalBookDemoSeeder: tamamlandi.\n";
        echo " - demo_user_email: " . self::DEMO_USER_EMAIL . "\n";
        echo " - demo_user_id: " . $user['id'] . "\n";
        echo " - demo_order_no: " . self::DEMO_ORDER_NO . "\n";
    }

    private function requiredTablesExist(): bool
    {
        foreach (['users', 'products', 'orders', 'order_items', 'digital_book_contents'] as $table) {
            if (! $this->db->tableExists($table)) {
                echo "DigitalBookDemoSeeder: eksik tablo -> {$table}\n";
                return false;
            }
        }

        return true;
    }

    private function upsertDemoUser(): array
    {
        $now = date('Y-m-d H:i:s');
        $builder = $this->db->table('users');

        $existing = $builder
            ->where('email', self::DEMO_USER_EMAIL)
            ->get()
            ->getRowArray();

        $data = [
            'username' => self::DEMO_USER_NAME,
            'email' => self::DEMO_USER_EMAIL,
            'password' => password_hash(self::DEMO_USER_PASSWORD, PASSWORD_DEFAULT),
            'role' => 'user',
            'status' => 'active',
            'updated_at' => $now,
            'deleted_at' => null,
        ];

        if (is_array($existing) && ! empty($existing['id'])) {
            $builder->where('id', (string) $existing['id'])->update($data);
            echo "user: update -> " . self::DEMO_USER_EMAIL . "\n";
            return ['id' => (string) $existing['id']] + $data;
        }

        $id = BaseUuidModel::uuidV4();
        $builder->insert($data + [
            'id' => $id,
            'created_at' => $now,
        ]);

        echo "user: create -> " . self::DEMO_USER_EMAIL . "\n";
        return ['id' => $id] + $data;
    }

    private function upsertDemoProducts(): array
    {
        $definitions = [
            [
                'code' => 'demo-dbr-zamanin-kiyisinda',
                'title' => 'Demo Dijital Kitap 1: Zamanın Kıyısında',
                'price' => 129.90,
                'summary' => 'DEMO-DBR katalog aciklamasi: Zaman ve hafiza temasinda test metni.',
            ],
            [
                'code' => 'demo-dbr-sessiz-kutuphane',
                'title' => 'Demo Dijital Kitap 2: Sessiz Kütüphane',
                'price' => 119.90,
                'summary' => 'DEMO-DBR katalog aciklamasi: Sessiz bir kutuphanede gecen test metni.',
            ],
            [
                'code' => 'demo-dbr-kod-ve-murekkep',
                'title' => 'Demo Dijital Kitap 3: Kod ve Mürekkep',
                'price' => 139.90,
                'summary' => 'DEMO-DBR katalog aciklamasi: Kod ve edebiyat birlesiminde test metni.',
            ],
        ];

        $categoryId = $this->resolveCategoryId();
        $now = date('Y-m-d H:i:s');
        $products = [];

        foreach ($definitions as $def) {
            $builder = $this->db->table('products');
            $existing = $builder
                ->where('product_name', $def['title'])
                ->where('author', self::DEMO_AUTHOR_MARKER)
                ->where('type', 'dijital')
                ->get()
                ->getRowArray();

            $data = [
                'author_id' => null,
                'type_id' => null,
                'category_id' => $categoryId,
                'product_name' => $def['title'],
                'author' => self::DEMO_AUTHOR_MARKER,
                'description' => $def['summary'],
                'price' => $def['price'],
                'stock_count' => 0,
                'reserved_count' => 0,
                'type' => 'dijital',
                'image' => null,
                'is_active' => 1,
                'stock' => 0,
                'updated_at' => $now,
                'deleted_at' => null,
            ];

            if (is_array($existing) && ! empty($existing['id'])) {
                $builder->where('id', (string) $existing['id'])->update($data);
                $id = (string) $existing['id'];
                echo "product: update -> {$def['code']} ({$id})\n";
            } else {
                $id = BaseUuidModel::uuidV4();
                $builder->insert($data + [
                    'id' => $id,
                    'created_at' => $now,
                ]);
                echo "product: create -> {$def['code']} ({$id})\n";
            }

            $products[] = [
                'code' => $def['code'],
                'id' => $id,
                'title' => $def['title'],
                'price' => (float) $def['price'],
                'type' => 'dijital',
            ];
        }

        return $products;
    }

    private function upsertDigitalContents(array $products): void
    {
        $now = date('Y-m-d H:i:s');

        foreach ($products as $product) {
            $content = $this->buildDemoContent((string) $product['title']);
            $builder = $this->db->table('digital_book_contents');
            $existing = $builder
                ->where('product_id', (string) $product['id'])
                ->where('deleted_at', null)
                ->orderBy('updated_at', 'DESC')
                ->orderBy('created_at', 'DESC')
                ->get()
                ->getRowArray();

            $data = [
                'product_id' => (string) $product['id'],
                'content_text' => $content,
                'source_type' => 'demo_seed',
                'updated_at' => $now,
                'deleted_at' => null,
            ];

            if (is_array($existing) && ! empty($existing['id'])) {
                $builder->where('id', (string) $existing['id'])->update($data);
                echo "content: update -> {$product['code']}\n";
            } else {
                $builder->insert($data + [
                    'id' => BaseUuidModel::uuidV4(),
                    'created_at' => $now,
                ]);
                echo "content: create -> {$product['code']}\n";
            }
        }
    }

    private function upsertDemoOrder(array $user, array $products): array
    {
        $now = date('Y-m-d H:i:s');
        $total = 0.0;
        foreach ($products as $product) {
            $total += (float) $product['price'];
        }

        $builder = $this->db->table('orders');
        $existing = $builder
            ->where('order_no', self::DEMO_ORDER_NO)
            ->get()
            ->getRowArray();

        $firstProductId = (string) ($products[0]['id'] ?? '');
        $itemCount = count($products);

        $data = [
            'order_no' => self::DEMO_ORDER_NO,
            'user_id' => (string) $user['id'],
            'product_id' => $firstProductId,
            'quantity' => $itemCount,
            'total_price' => $total,
            'total_amount' => $total,
            'customer_name' => self::DEMO_USER_NAME,
            'payment_method' => 'mock',
            'payment_status' => 'paid',
            'status' => 'reserved',
            'order_status' => 'preparing',
            'shipping_status' => 'not_shipped',
            'fulfillment_status' => 'PENDING',
            'item_count' => $itemCount,
            'subtotal_amount' => $total,
            'shipping_amount' => 0.00,
            'discount_amount' => 0.00,
            'currency' => 'TRY',
            'order_date' => $now,
            'paid_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ];

        if (is_array($existing) && ! empty($existing['id'])) {
            $builder->where('id', (string) $existing['id'])->update($data);
            $id = (string) $existing['id'];
            echo "order: update -> " . self::DEMO_ORDER_NO . " ({$id})\n";
        } else {
            $id = BaseUuidModel::uuidV4();
            $builder->insert($data + [
                'id' => $id,
                'created_at' => $now,
            ]);
            echo "order: create -> " . self::DEMO_ORDER_NO . " ({$id})\n";
        }

        return ['id' => $id] + $data;
    }

    private function upsertDemoOrderItems(string $orderId, array $products): void
    {
        $now = date('Y-m-d H:i:s');
        $builder = $this->db->table('order_items');

        foreach ($products as $product) {
            $existing = $builder
                ->where('order_id', $orderId)
                ->where('product_id', (string) $product['id'])
                ->get()
                ->getRowArray();

            $data = [
                'order_id' => $orderId,
                'product_id' => (string) $product['id'],
                'product_name_snapshot' => (string) $product['title'],
                'author' => self::DEMO_AUTHOR_MARKER,
                'product_image' => null,
                'product_type' => 'dijital',
                'unit_price' => (float) $product['price'],
                'quantity' => 1,
                'line_total' => (float) $product['price'],
                'item_status' => 'PREPARING',
                'updated_at' => $now,
                'deleted_at' => null,
            ];

            if (is_array($existing) && ! empty($existing['id'])) {
                $builder->where('id', (string) $existing['id'])->update($data);
                echo "order_item: update -> {$product['code']}\n";
            } else {
                $builder->insert($data + [
                    'id' => BaseUuidModel::uuidV4(),
                    'created_at' => $now,
                ]);
                echo "order_item: create -> {$product['code']}\n";
            }
        }
    }

    private function resolveCategoryId(): ?string
    {
        $builder = $this->db->table('categories')
            ->select('id')
            ->limit(1);

        if ($this->columnExists('categories', 'deleted_at')) {
            $builder->where('deleted_at', null);
        }

        if ($this->columnExists('categories', 'created_at')) {
            $builder->orderBy('created_at', 'ASC');
        } else {
            $builder->orderBy('id', 'ASC');
        }

        $row = $builder->get()->getRowArray();

        if (is_array($row) && ! empty($row['id'])) {
            return (string) $row['id'];
        }

        return null;
    }

    private function columnExists(string $table, string $column): bool
    {
        $result = $this->db->query("SHOW COLUMNS FROM `{$table}` LIKE ?", [$column])->getRowArray();

        return is_array($result) && ! empty($result['Field']);
    }

    private function buildDemoContent(string $title): string
    {
        $sections = [];
        for ($chapter = 1; $chapter <= 10; $chapter++) {
            $paragraphs = [];
            for ($p = 1; $p <= 3; $p++) {
                $paragraphs[] = "{$title} icin hazirlanan demo metin bolumu {$chapter}.{$p}. "
                    . "Bu paragraf okuyucu panelindeki sayfa gecisi, offset bazli highlight ve oturum guvenligi davranisini test etmek icin uretilmistir. "
                    . "Metin yapaydir, test/discovery amaciyla kullanilir ve production icerik yerine gecmez. "
                    . "Cumle uzunluklari ve bosluk dagilimi kasitli olarak cesitlendirilerek birden fazla chunk olusmasi saglanir.";
            }
            $sections[] = "Bolum {$chapter}\n\n" . implode("\n\n", $paragraphs);
        }

        return implode("\n\n---\n\n", $sections);
    }
}
