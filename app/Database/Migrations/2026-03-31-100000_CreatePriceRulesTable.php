<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePriceRulesTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('price_rules')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'CHAR', 'constraint' => 36],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'type' => ['type' => 'VARCHAR', 'constraint' => 16, 'comment' => 'percentage|fixed'],
            'value' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'target' => ['type' => 'VARCHAR', 'constraint' => 16, 'default' => 'global'],
            'target_id' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'priority' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['is_active', 'target'], false, false, 'idx_price_rules_active_target');
        $this->forge->addKey('priority', false, false, 'idx_price_rules_priority');
        $this->forge->createTable('price_rules', true);
    }

    public function down()
    {
        $this->forge->dropTable('price_rules', true);
    }
}
