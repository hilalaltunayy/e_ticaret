<?php

namespace App\Database\Seeds;

use App\Models\BaseUuidModel;
use CodeIgniter\Database\Seeder;

class MarketingCouponsSeeder extends Seeder
{
    public function run()
    {
        if (! $this->db->tableExists('coupons')) {
            echo "MarketingCouponsSeeder: coupons tablosu bulunamadı.\n";
            return;
        }

        $now = date('Y-m-d H:i:s');
        $couponsTable = $this->db->table('coupons');
        $targetsTable = $this->db->table('coupon_targets');

        $seedCoupons = [
            [
                'code' => 'WELCOME10',
                'coupon_kind' => 'discount',
                'discount_type' => 'percent',
                'discount_value' => 10,
                'min_cart_amount' => 0,
                'max_usage_total' => 500,
                'max_usage_per_user' => 1,
                'starts_at' => null,
                'ends_at' => null,
                'is_active' => 1,
                'is_first_order_only' => 1,
            ],
            [
                'code' => 'KARGO100',
                'coupon_kind' => 'free_shipping',
                'discount_type' => 'none',
                'discount_value' => 0,
                'min_cart_amount' => 100,
                'max_usage_total' => 1000,
                'max_usage_per_user' => 3,
                'starts_at' => null,
                'ends_at' => null,
                'is_active' => 1,
                'is_first_order_only' => 0,
            ],
            [
                'code' => 'KITAP20',
                'coupon_kind' => 'discount',
                'discount_type' => 'percent',
                'discount_value' => 20,
                'min_cart_amount' => 200,
                'max_usage_total' => 200,
                'max_usage_per_user' => 2,
                'starts_at' => null,
                'ends_at' => null,
                'is_active' => 1,
                'is_first_order_only' => 0,
            ],
        ];

        $kitapCategoryId = $this->resolveBookCategoryId();
        $inserted = 0;
        foreach ($seedCoupons as $item) {
            $code = strtoupper(trim((string) $item['code']));
            $exists = $couponsTable->select('id')->where('code', $code)->get()->getRowArray();
            if (is_array($exists) && isset($exists['id'])) {
                continue;
            }

            $couponId = BaseUuidModel::uuidV4();
            $couponsTable->insert([
                'id' => $couponId,
                'code' => $code,
                'coupon_kind' => $item['coupon_kind'],
                'discount_type' => $item['discount_type'],
                'discount_value' => $item['discount_value'],
                'min_cart_amount' => $item['min_cart_amount'],
                'max_usage_total' => $item['max_usage_total'],
                'max_usage_per_user' => $item['max_usage_per_user'],
                'starts_at' => $item['starts_at'],
                'ends_at' => $item['ends_at'],
                'is_active' => $item['is_active'],
                'is_first_order_only' => $item['is_first_order_only'],
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);
            $inserted++;

            if ($code === 'KITAP20' && $kitapCategoryId !== null) {
                $targetsTable->insert([
                    'id' => BaseUuidModel::uuidV4(),
                    'coupon_id' => $couponId,
                    'target_type' => 'category',
                    'target_id' => $kitapCategoryId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        echo "MarketingCouponsSeeder: {$inserted} kupon eklendi.\n";
    }

    private function resolveBookCategoryId(): ?string
    {
        if (! $this->db->tableExists('categories')) {
            return null;
        }

        $row = $this->db->table('categories')
            ->select('id')
            ->groupStart()
            ->like('LOWER(category_name)', 'kitap')
            ->orLike('LOWER(category_name)', 'book')
            ->groupEnd()
            ->orderBy('category_name', 'ASC')
            ->get()
            ->getRowArray();

        if (is_array($row) && isset($row['id'])) {
            return (string) $row['id'];
        }

        $first = $this->db->table('categories')->select('id')->orderBy('category_name', 'ASC')->get()->getRowArray();
        if (is_array($first) && isset($first['id'])) {
            return (string) $first['id'];
        }

        return null;
    }
}

