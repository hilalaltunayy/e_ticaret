<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDashboardBlockInstancesTable extends Migration
{
   public function up()
{
    if (! $this->db->tableExists('dashboard_block_instances')) {
        $this->forge->addField([
            'id' => [
                'type' => 'CHAR',
                'constraint' => 36,
            ],
            'dashboard_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
            ],
            'block_type_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
            ],
            'order_index' => [
                'type' => 'INT',
                'default' => 0,
            ],
            'position_x' => [
                'type' => 'INT',
                'default' => 0,
            ],
            'position_y' => [
                'type' => 'INT',
                'default' => 0,
            ],
            'config_json' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('dashboard_block_instances');
    }
}

public function down()
{
    $this->forge->dropTable('dashboard_block_instances');
}
}
