<?php

namespace App\Database\Seeds;

use App\Models\BaseUuidModel;
use CodeIgniter\Database\Seeder;

class DashboardBuilderSeeder extends Seeder
{
    public function run()
    {
        if (! $this->db->tableExists('dashboards') || ! $this->db->tableExists('dashboard_block_types')) {
            echo "DashboardBuilderSeeder: gerekli tablolar bulunamadi.\n";
            return;
        }

        $now = date('Y-m-d H:i:s');

        $dashboardTable = $this->db->table('dashboards');
        $existingDashboard = $dashboardTable
            ->select('id')
            ->where('name', 'Varsayılan Dashboard')
            ->get()
            ->getRowArray();

        if (! (is_array($existingDashboard) && isset($existingDashboard['id']))) {
            $dashboardTable->insert([
                'id' => BaseUuidModel::uuidV4(),
                'user_id' => null,
                'name' => 'Varsayılan Dashboard',
                'description' => 'Dashboard builder için başlangıç alanı.',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $typesTable = $this->db->table('dashboard_block_types');
        $types = [
            [
                'code' => 'stat_card',
                'name' => 'Stat Card',
                'description' => 'Tek metrik gösteren özet blok.',
                'default_config' => json_encode(['metric' => 'orders_total']),
            ],
            [
                'code' => 'chart',
                'name' => 'Chart',
                'description' => 'Grafik gösteren blok.',
                'default_config' => json_encode(['chart_type' => 'line']),
            ],
            [
                'code' => 'note',
                'name' => 'Note',
                'description' => 'Serbest not veya açıklama bloğu.',
                'default_config' => json_encode(['content' => '']),
            ],
        ];

        foreach ($types as $type) {
            $exists = $typesTable->select('id')->where('code', $type['code'])->get()->getRowArray();
            if (is_array($exists) && isset($exists['id'])) {
                continue;
            }

            $typesTable->insert([
                'id' => BaseUuidModel::uuidV4(),
                'code' => $type['code'],
                'name' => $type['name'],
                'description' => $type['description'],
                'default_config' => $type['default_config'],
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        echo "DashboardBuilderSeeder: varsayılan dashboard ve block type kayıtları hazırlandı.\n";
    }
}
