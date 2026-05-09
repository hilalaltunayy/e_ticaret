<?php

namespace App\Database\Seeds;

use App\Models\BaseUuidModel;
use CodeIgniter\Database\Seeder;

class ProductReviewSeeder extends Seeder
{
    public function run()
    {
        if (! $this->requiredTablesExist()) {
            echo "ProductReviewSeeder: gerekli tablolar bulunamadi.\n";
            return;
        }

        $users = $this->resolveUsers();
        $products = $this->resolveProducts();

        if ($users === [] || $products === []) {
            echo "ProductReviewSeeder: uygun demo kullanici veya urun bulunamadi.\n";
            return;
        }

        $records = $this->buildSeedRecords($users, $products);
        if ($records === []) {
            echo "ProductReviewSeeder: olusturulacak demo yorum kaydi bulunamadi.\n";
            return;
        }

        foreach ($records as $record) {
            $this->upsertReview($record);
        }

        echo "ProductReviewSeeder: demo yorumlar hazirlandi.\n";
    }

    private function requiredTablesExist(): bool
    {
        foreach (['product_reviews', 'products', 'users'] as $table) {
            if (! $this->db->tableExists($table)) {
                return false;
            }
        }

        return true;
    }

    private function resolveUsers(): array
    {
        $resolved = [];
        $seen = [];

        foreach (['orders.test@test.local', 'user@site.com'] as $email) {
            $row = $this->db->table('users')
                ->where('email', $email)
                ->where('deleted_at', null)
                ->get()
                ->getRowArray();

            if (! is_array($row) || empty($row['id'])) {
                continue;
            }

            $id = (string) $row['id'];
            if (isset($seen[$id])) {
                continue;
            }

            $resolved[] = $row;
            $seen[$id] = true;
        }

        if (count($resolved) >= 2) {
            return $resolved;
        }

        $fallbackRows = $this->db->table('users')
            ->where('deleted_at', null)
            ->where('status', 'active')
            ->whereNotIn('role', ['admin', 'secretary'])
            ->orderBy('created_at', 'ASC')
            ->limit(3)
            ->get()
            ->getResultArray();

        foreach ($fallbackRows as $row) {
            $id = trim((string) ($row['id'] ?? ''));
            if ($id === '' || isset($seen[$id])) {
                continue;
            }

            $resolved[] = $row;
            $seen[$id] = true;
        }

        return $resolved;
    }

    private function resolveProducts(): array
    {
        $resolved = [];
        $seen = [];

        $preferredNames = [
            'Sipariş Test Romanı',
            'Sipariş Test E-Kitap',
            'Sipariş Test Çocuk Kitabı',
        ];

        foreach ($preferredNames as $name) {
            $row = $this->db->table('products')
                ->where('product_name', $name)
                ->where('deleted_at', null)
                ->where('is_active', 1)
                ->get()
                ->getRowArray();

            if (! is_array($row) || empty($row['id'])) {
                continue;
            }

            $id = (string) $row['id'];
            if (isset($seen[$id])) {
                continue;
            }

            $resolved[] = $row;
            $seen[$id] = true;
        }

        if (count($resolved) >= 3) {
            return $resolved;
        }

        $fallbackRows = $this->db->table('products')
            ->where('deleted_at', null)
            ->where('is_active', 1)
            ->orderBy('created_at', 'ASC')
            ->limit(5)
            ->get()
            ->getResultArray();

        foreach ($fallbackRows as $row) {
            $id = trim((string) ($row['id'] ?? ''));
            if ($id === '' || isset($seen[$id])) {
                continue;
            }

            $resolved[] = $row;
            $seen[$id] = true;
        }

        return $resolved;
    }

    private function buildSeedRecords(array $users, array $products): array
    {
        $records = [];

        if (! empty($users[0]) && ! empty($products[0])) {
            $records[] = [
                'user_id' => (string) $users[0]['id'],
                'product_id' => (string) $products[0]['id'],
                'rating' => 5,
                'title' => 'Demo Okur Yorumu - Harika Deneyim',
                'comment' => 'Kurgusu akici, baski kalitesi de gayet iyi. Gonul rahatligiyla tavsiye ederim.',
                'status' => 'approved',
                'created_at' => '2026-05-02 11:15:00',
            ];
        }

        if (! empty($users[1]) && ! empty($products[0])) {
            $records[] = [
                'user_id' => (string) $users[1]['id'],
                'product_id' => (string) $products[0]['id'],
                'rating' => 3,
                'title' => 'Demo Okur Yorumu - Dengeli',
                'comment' => 'Anlatim guclu ama tempo yer yer dusuyor. Yine de keyifle okudum.',
                'status' => 'approved',
                'created_at' => '2026-05-03 14:40:00',
            ];
        }

        if (! empty($users[0]) && ! empty($products[1])) {
            $records[] = [
                'user_id' => (string) $users[0]['id'],
                'product_id' => (string) $products[1]['id'],
                'rating' => 4,
                'title' => 'Demo Okur Yorumu - Hızlı Erişim',
                'comment' => 'Dijital urun olarak sorunsuz bir deneyim sundu. Icerik beklentimi karsiladi.',
                'status' => 'approved',
                'created_at' => '2026-05-04 09:20:00',
            ];
        }

        if (! empty($users[1] ?? $users[0]) && ! empty($products[2] ?? $products[1] ?? $products[0])) {
            $pendingUser = ! empty($users[1]) ? $users[1] : $users[0];
            $pendingProduct = ! empty($products[2]) ? $products[2] : (! empty($products[1]) ? $products[1] : $products[0]);

            $records[] = [
                'user_id' => (string) $pendingUser['id'],
                'product_id' => (string) $pendingProduct['id'],
                'rating' => 2,
                'title' => 'Demo Moderasyon Yorumu - Beklemede',
                'comment' => 'Bu yorum admin moderasyon ekraninda bekleyen kayit olarak gorunmelidir.',
                'status' => 'pending',
                'created_at' => '2026-05-05 16:05:00',
            ];
        }

        return $records;
    }

    private function upsertReview(array $record): void
    {
        $builder = $this->db->table('product_reviews');
        $existing = $builder
            ->where('user_id', (string) $record['user_id'])
            ->where('product_id', (string) $record['product_id'])
            ->where('title', (string) $record['title'])
            ->get()
            ->getRowArray();

        $data = [
            'user_id' => (string) $record['user_id'],
            'product_id' => (string) $record['product_id'],
            'order_id' => null,
            'order_item_id' => null,
            'rating' => (int) $record['rating'],
            'title' => (string) $record['title'],
            'comment' => (string) $record['comment'],
            'status' => (string) $record['status'],
            'updated_at' => date('Y-m-d H:i:s'),
            'deleted_at' => null,
        ];

        if (is_array($existing) && ! empty($existing['id'])) {
            $builder->where('id', (string) $existing['id'])->update($data);
            return;
        }

        $builder->insert($data + [
            'id' => BaseUuidModel::uuidV4(),
            'created_at' => (string) $record['created_at'],
        ]);
    }
}
