<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBlockTypesTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('block_types')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'CHAR', 'constraint' => 36],
            'code' => ['type' => 'VARCHAR', 'constraint' => 64],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => ['type' => 'TEXT', 'null' => true],
            'schema_json' => ['type' => 'TEXT', 'null' => true],
            'default_config_json' => ['type' => 'TEXT', 'null' => true],
            'allowed_zones' => ['type' => 'TEXT', 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code', 'uq_block_types_code');
        $this->forge->addKey('is_active', false, false, 'idx_block_types_is_active');
        $this->forge->createTable('block_types', true);
    }

    public function down()
    {
        $this->forge->dropTable('block_types', true);
    }
}
