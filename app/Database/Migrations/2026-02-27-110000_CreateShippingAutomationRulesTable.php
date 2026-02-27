<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShippingAutomationRulesTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('shipping_automation_rules')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'CHAR', 'constraint' => 36],
            'rule_type' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false],
            'city' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'desi_min' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'desi_max' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'sla_days' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'primary_company_id' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'secondary_company_id' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('rule_type', false, false, 'idx_sar_rule_type');
        $this->forge->addKey('city', false, false, 'idx_sar_city');
        $this->forge->addKey('is_active', false, false, 'idx_sar_is_active');
        $this->forge->createTable('shipping_automation_rules', true);
    }

    public function down()
    {
        if ($this->db->tableExists('shipping_automation_rules')) {
            $this->forge->dropTable('shipping_automation_rules', true);
        }
    }
}
