<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class OrdersTestSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // Ürün var mı?
        $productIds = array_map(
            fn($r) => (int)$r['id'],
            $db->table('products')->select('id')->limit(20)->get()->getResultArray()
        );

        if (empty($productIds)) {
            // Ürün yoksa seed'i güvenli şekilde durdur.
            // (İstersen burada products seed'i de yazıp otomatik ekleriz)
            echo "products tablosu boş. Önce en az 1 ürün ekle, sonra tekrar seed çalıştır.\n";
            return;
        }

        $now = date('Y-m-d H:i:s');

        // Bugün + bu hafta içinden 5 sipariş
        $dates = [
            date('Y-m-d'), // bugün
            date('Y-m-d', strtotime('-1 day')),
            date('Y-m-d', strtotime('-2 day')),
            date('Y-m-d', strtotime('-3 day')),
            date('Y-m-d', strtotime('-5 day')),
        ];

        $statuses = ['pending', 'completed', 'completed', 'pending', 'cancelled'];

        foreach ($dates as $i => $orderDate) {
            $productId = $productIds[array_rand($productIds)];
            $qty = rand(1, 3);

            // Basit hesap: products.price * qty (price yoksa 100 varsay)
            $p = $db->table('products')->select('price, product_name')->where('id', $productId)->get()->getRowArray();
            $price = (float)($p['price'] ?? 100);
            $total = $price * $qty;

            $db->table('orders')->insert([
                'product_id'    => $productId,
                'quantity'      => $qty,
                'total_price'   => $total,      // sende var
                'total_amount'  => $total,      // sende var
                'order_date'    => $orderDate,  // DATE
                'customer_name' => 'Test Customer ' . ($i + 1),
                'status'        => $statuses[$i] ?? 'pending',
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }

        echo "OrdersTestSeeder: 5 order eklendi.\n";
    }
}