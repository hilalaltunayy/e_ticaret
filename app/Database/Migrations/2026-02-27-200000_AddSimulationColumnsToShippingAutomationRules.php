<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSimulationColumnsToShippingAutomationRules extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('shipping_automation_rules')) {
            return;
        }

        $fields = $this->db->getFieldNames('shipping_automation_rules');

        $add = [];

        if (! in_array('city_slug', $fields, true)) {
            $add['city_slug'] = [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
            ];
        }

        if (! in_array('sla_max_days', $fields, true)) {
            $add['sla_max_days'] = [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ];
        }

        if (! in_array('supports_cod', $fields, true)) {
            $add['supports_cod'] = [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ];
        }

        if (! in_array('estimated_cost', $fields, true)) {
            $add['estimated_cost'] = [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
            ];
        }

        if (! in_array('priority', $fields, true)) {
            $add['priority'] = [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ];
        }

        if (! in_array('config_json', $fields, true)) {
            $add['config_json'] = [
                'type' => 'TEXT',
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
            ];
        }

        if ($add !== []) {
            $this->forge->addColumn('shipping_automation_rules', $add);
        }

        $this->db->query('ALTER TABLE shipping_automation_rules CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        $indexes = array_map(static fn (array $row): string => (string) ($row['Key_name'] ?? ''), $this->db->query('SHOW INDEX FROM shipping_automation_rules')->getResultArray());

        if (! in_array('idx_sar_city_slug', $indexes, true)) {
            $this->db->query('CREATE INDEX idx_sar_city_slug ON shipping_automation_rules (city_slug)');
        }
        if (! in_array('idx_sar_sla_max_days', $indexes, true)) {
            $this->db->query('CREATE INDEX idx_sar_sla_max_days ON shipping_automation_rules (sla_max_days)');
        }
        if (! in_array('idx_sar_supports_cod', $indexes, true)) {
            $this->db->query('CREATE INDEX idx_sar_supports_cod ON shipping_automation_rules (supports_cod)');
        }
        if (! in_array('idx_sar_priority', $indexes, true)) {
            $this->db->query('CREATE INDEX idx_sar_priority ON shipping_automation_rules (priority)');
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('shipping_automation_rules')) {
            return;
        }

        $fields = $this->db->getFieldNames('shipping_automation_rules');

        foreach (['city_slug', 'sla_max_days', 'supports_cod', 'estimated_cost', 'priority', 'config_json'] as $field) {
            if (in_array($field, $fields, true)) {
                $this->forge->dropColumn('shipping_automation_rules', $field);
            }
        }

        foreach (['idx_sar_city_slug', 'idx_sar_sla_max_days', 'idx_sar_supports_cod', 'idx_sar_priority'] as $index) {
            try {
                $this->db->query('DROP INDEX ' . $index . ' ON shipping_automation_rules');
            } catch (\Throwable) {
            }
        }
    }
}