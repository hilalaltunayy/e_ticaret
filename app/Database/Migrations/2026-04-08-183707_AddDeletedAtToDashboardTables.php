<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeletedAtToDashboardTables extends Migration
{
    public function up()
    {
        $tables = [
            'dashboards',
            'dashboard_block_types',
            'dashboard_block_instances',
        ];

        foreach ($tables as $table) {
            if (! $this->db->tableExists($table)) {
                continue;
            }

            if (! $this->db->fieldExists('deleted_at', $table)) {
                $this->forge->addColumn($table, [
                    'deleted_at' => [
                        'type' => 'DATETIME',
                        'null' => true,
                    ],
                ]);
            }
        }
    }

    public function down()
    {
        $tables = [
            'dashboards',
            'dashboard_block_types',
            'dashboard_block_instances',
        ];

        foreach ($tables as $table) {
            if (! $this->db->tableExists($table)) {
                continue;
            }

            if ($this->db->fieldExists('deleted_at', $table)) {
                $this->forge->dropColumn($table, 'deleted_at');
            }
        }
    }
}