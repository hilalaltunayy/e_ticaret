<?php

namespace App\Database\Seeds;

use App\Models\BaseUuidModel;
use CodeIgniter\Database\Seeder;

class CartPriceDropTestSeeder extends Seeder
{
    private const TEST_EMAIL = 'cart.pricedrop@test.local';
    private const TEST_PASSWORD = 'Test12345!';
    private const PRICE_DROP_PRODUCT = 'Sepet Fiyat Düşüş Test Kitabı';
    private const NORMAL_PRICE_PRODUCT = 'Sepet Normal Fiyat Test Kitabı';

    public function run()
    {
        if (! $this->requiredTablesExist()) {
            echo "CartPriceDropTestSeeder: gerekli tablolar bulunamadi.\n";
            return;
        }

        $userId = $this->upsertTestUser();
        $priceDropProductId = $this->upsertPriceDropProduct();
        $normalProductId = $this->upsertNormalPriceProduct();
        $cartId = $this->upsertActiveCart($userId);

        $this->upsertCartItem($cartId, $priceDropProductId, 2, 250.00);
        $this->upsertCartItem($cartId, $normalProductId, 1, 180.00);

        echo "CartPriceDropTestSeeder: test kullanicisi ve sepet verileri hazirlandi.\n";
        echo 'Login email: ' . self::TEST_EMAIL . "\n";
        echo 'Login password: ' . self::TEST_PASSWORD . "\n";
    }

    private function requiredTablesExist(): bool
    {
        foreach (['users', 'products', 'carts', 'cart_items'] as $table) {
            if (! $this->db->tableExists($table)) {
                return false;
            }
        }

        return true;
    }

    private function upsertTestUser(): string
    {
        $builder = $this->db->table('users');
        $user = $builder
            ->where('email', self::TEST_EMAIL)
            ->get()
            ->getRowArray();

        $data = [
            'username' => 'Cart Price Drop Test',
            'email' => self::TEST_EMAIL,
            'password' => password_hash(self::TEST_PASSWORD, PASSWORD_DEFAULT),
            'role' => 'user',
            'status' => 'active',
            'updated_at' => date('Y-m-d H:i:s'),
            'deleted_at' => null,
        ];

        if (is_array($user) && ! empty($user['id'])) {
            $builder->where('id', (string) $user['id'])->update($data);
            return (string) $user['id'];
        }

        $id = BaseUuidModel::uuidV4();
        $builder->insert(array_merge($data, [
            'id' => $id,
            'created_at' => date('Y-m-d H:i:s'),
        ]));

        return $id;
    }

    private function upsertPriceDropProduct(): string
    {
        return $this->upsertProduct(
            self::PRICE_DROP_PRODUCT,
            200.00,
            'Bu test ürünü, sepet fiyat düşüşü ve kazanç görünümünü doğrulamak için kullanılır.',
            'Cart Test Yazarı'
        );
    }

    private function upsertNormalPriceProduct(): string
    {
        return $this->upsertProduct(
            self::NORMAL_PRICE_PRODUCT,
            180.00,
            'Bu test ürünü, sepet içinde normal fiyat senaryosunu doğrulamak için kullanılır.',
            'Cart Test Yazarı'
        );
    }

    private function upsertProduct(string $productName, float $price, string $description, string $author): string
    {
        $builder = $this->db->table('products');
        $product = $builder
            ->where('product_name', $productName)
            ->get()
            ->getRowArray();

        $data = [
            'product_name' => $productName,
            'author' => $author,
            'description' => $description,
            'price' => $price,
            'stock_count' => 10,
            'stock' => 10,
            'type' => 'basili',
            'image' => null,
            'is_active' => 1,
            'updated_at' => date('Y-m-d H:i:s'),
            'deleted_at' => null,
        ];

        if (is_array($product) && ! empty($product['id'])) {
            $builder->where('id', (string) $product['id'])->update($data);
            return (string) $product['id'];
        }

        $id = BaseUuidModel::uuidV4();
        $builder->insert(array_merge($data, [
            'id' => $id,
            'created_at' => date('Y-m-d H:i:s'),
        ]));

        return $id;
    }

    private function upsertActiveCart(string $userId): string
    {
        $builder = $this->db->table('carts');
        $cart = $builder
            ->where('user_id', $userId)
            ->where('status', 'ACTIVE')
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        $data = [
            'user_id' => $userId,
            'status' => 'ACTIVE',
            'currency' => 'TRY',
            'updated_at' => date('Y-m-d H:i:s'),
            'deleted_at' => null,
        ];

        if (is_array($cart) && ! empty($cart['id'])) {
            $builder->where('id', (string) $cart['id'])->update($data);
            return (string) $cart['id'];
        }

        $id = BaseUuidModel::uuidV4();
        $builder->insert(array_merge($data, [
            'id' => $id,
            'created_at' => date('Y-m-d H:i:s'),
        ]));

        return $id;
    }

    private function upsertCartItem(string $cartId, string $productId, int $quantity, float $snapshotPrice): void
    {
        $builder = $this->db->table('cart_items');
        $item = $builder
            ->where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->get()
            ->getRowArray();

        $data = [
            'cart_id' => $cartId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'unit_price_snapshot' => $snapshotPrice,
            'updated_at' => date('Y-m-d H:i:s'),
            'deleted_at' => null,
        ];

        if (is_array($item) && ! empty($item['id'])) {
            $builder->where('id', (string) $item['id'])->update($data);
            return;
        }

        $builder->insert(array_merge($data, [
            'id' => BaseUuidModel::uuidV4(),
            'created_at' => date('Y-m-d H:i:s'),
        ]));
    }
}
