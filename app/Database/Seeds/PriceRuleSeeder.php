<?php

namespace App\Database\Seeds;

use App\Models\BaseUuidModel;
use CodeIgniter\Database\Seeder;

class PriceRuleSeeder extends Seeder
{
    public function run()
    {
        if (! $this->db->tableExists('price_rules')) {
            echo "PriceRuleSeeder: price_rules tablosu bulunamadi.\n";
            return;
        }

        $table = $this->db->table('price_rules');
        $existing = $table
            ->select('id')
            ->where('name', 'Test Indirim')
            ->where('target', 'global')
            ->get()
            ->getRowArray();

        if (is_array($existing) && isset($existing['id'])) {
            echo "PriceRuleSeeder: test kaydi zaten mevcut.\n";
            return;
        }

        $now = date('Y-m-d H:i:s');

        $table->insert([
            'id' => BaseUuidModel::uuidV4(),
            'name' => 'Test Indirim',
            'type' => 'percentage',
            'value' => 10,
            'target' => 'global',
            'target_id' => null,
            'priority' => 1,
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        echo "PriceRuleSeeder: 1 fiyat kurali eklendi.\n";
    }
}
