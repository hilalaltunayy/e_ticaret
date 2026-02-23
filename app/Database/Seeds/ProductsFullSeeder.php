<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProductsFullSeeder extends Seeder
{
    public function run()
    {
        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        // -------------------------
        // TYPES (id, name)  -> timestamps YOK
        // -------------------------
        foreach (['physical', 'digital'] as $name) {
            $exists = $db->table('types')->select('id')->where('name', $name)->get()->getRowArray();
            if (!$exists) {
                $db->table('types')->insert(['name' => $name]);
            }
        }

        $typeRows = $db->table('types')->select('id,name')->get()->getResultArray();
        $typeMap = [];
        foreach ($typeRows as $r) $typeMap[$r['name']] = (int)$r['id'];

        // -------------------------
        // CATEGORIES (id, category_name) -> timestamps YOK
        // -------------------------
        foreach (['Roman', 'Bilim', 'Yazılım'] as $catName) {
            $exists = $db->table('categories')->select('id')->where('category_name', $catName)->get()->getRowArray();
            if (!$exists) {
                $db->table('categories')->insert(['category_name' => $catName]);
            }
        }

        $categoryRows = $db->table('categories')->select('id')->get()->getResultArray();
        $categoryIds = array_map(fn($r) => (int)$r['id'], $categoryRows);

        // -------------------------
        // AUTHORS (id, name, bio, created_at, updated_at, deleted_at)
        // -------------------------
        $authors = [
            ['name' => 'Ahmet Yılmaz', 'bio' => 'Test yazar'],
            ['name' => 'Ayşe Demir',   'bio' => 'Test yazar'],
        ];

        foreach ($authors as $a) {
            $exists = $db->table('authors')->select('id')->where('name', $a['name'])->get()->getRowArray();
            if (!$exists) {
                $db->table('authors')->insert([
                    'name'       => $a['name'],
                    'bio'        => $a['bio'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $authorRows = $db->table('authors')->select('id')->get()->getResultArray();
        $authorIds = array_map(fn($r) => (int)$r['id'], $authorRows);

        // -------------------------
        // PRODUCTS (senin kolonlar)
        // -------------------------
        $products = [
            ['product_name' => 'Laravel Öğreniyorum', 'type' => 'digital'],
            ['product_name' => 'Modern Roman',        'type' => 'physical'],
            ['product_name' => 'Bilim Dünyası',       'type' => 'physical'],
        ];

        foreach ($products as $p) {
            $exists = $db->table('products')->select('id')->where('product_name', $p['product_name'])->get()->getRowArray();
            if ($exists) continue;

            $db->table('products')->insert([
                'author_id'    => $authorIds[array_rand($authorIds)],
                'type_id'      => $typeMap[$p['type']] ?? null,
                'category_id'  => $categoryIds ? $categoryIds[array_rand($categoryIds)] : null,

                'product_name' => $p['product_name'],
                'author'       => 'Seeder Author',
                'description'  => 'Seeder açıklama',
                'price'        => rand(50, 200),
                'stock_count'  => 100,

                'type'         => $p['type'], // string alanın da var
                'image'        => null,
                'is_active'    => 1,
                'stock'        => 100,

                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }

        echo "ProductsFullSeeder: types/categories/authors/products eklendi.\n";
    }
}