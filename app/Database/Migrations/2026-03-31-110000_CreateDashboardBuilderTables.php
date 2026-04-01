<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDashboardBuilderTables extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('dashboards')) {
            $this->forge->addField([
                'id' => ['type' => 'CHAR', 'constraint' => 36],
                'user_id' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
                'name' => ['type' => 'VARCHAR', 'constraint' => 255],
                'description' => ['type' => 'TEXT', 'null' => true],
                'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('user_id', false, false, 'idx_dashboards_user_id');
            $this->forge->addKey('is_active', false, false, 'idx_dashboards_is_active');
            $this->forge->createTable('dashboards', true);
        }

        if (! $this->db->tableExists('dashboard_block_types')) {
            $this->forge->addField([
                'id' => ['type' => 'CHAR', 'constraint' => 36],
                'code' => ['type' => 'VARCHAR', 'constraint' => 64],
                'name' => ['type' => 'VARCHAR', 'constraint' => 255],
                'description' => ['type' => 'TEXT', 'null' => true],
                'default_config' => ['type' => 'TEXT', 'null' => true],
                'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('code', 'uq_dashboard_block_types_code');
            $this->forge->addKey('is_active', false, false, 'idx_dashboard_block_types_is_active');
            $this->forge->createTable('dashboard_block_types', true);
        }

        if (! $this->db->tableExists('dashboard_blocks')) {
            $this->forge->addField([
                'id' => ['type' => 'CHAR', 'constraint' => 36],
                'dashboard_id' => ['type' => 'CHAR', 'constraint' => 36],
                'block_type_id' => ['type' => 'CHAR', 'constraint' => 36],
                'title' => ['type' => 'VARCHAR', 'constraint' => 255],
                'config_json' => ['type' => 'TEXT', 'null' => true],
                'position_x' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'position_y' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'width' => ['type' => 'INT', 'constraint' => 11, 'default' => 4],
                'height' => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
                'order_index' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'is_visible' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('dashboard_id', false, false, 'idx_dashboard_blocks_dashboard_id');
            $this->forge->addKey('block_type_id', false, false, 'idx_dashboard_blocks_block_type_id');
            $this->forge->addKey('order_index', false, false, 'idx_dashboard_blocks_order_index');
            $this->forge->addKey('is_visible', false, false, 'idx_dashboard_blocks_is_visible');
            $this->forge->createTable('dashboard_blocks', true);
        }

        if ($this->db->tableExists('dashboard_blocks') && $this->db->tableExists('dashboards')) {
            try {
                $this->db->query("ALTER TABLE dashboard_blocks
                    ADD CONSTRAINT fk_dashboard_blocks_dashboard
                    FOREIGN KEY (dashboard_id) REFERENCES dashboards(id)
                    ON DELETE CASCADE ON UPDATE CASCADE");
            } catch (\Throwable $e) {
            }
        }

        if ($this->db->tableExists('dashboard_blocks') && $this->db->tableExists('dashboard_block_types')) {
            try {
                $this->db->query("ALTER TABLE dashboard_blocks
                    ADD CONSTRAINT fk_dashboard_blocks_block_type
                    FOREIGN KEY (block_type_id) REFERENCES dashboard_block_types(id)
                    ON DELETE RESTRICT ON UPDATE CASCADE");
            } catch (\Throwable $e) {
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('dashboard_blocks', true);
        $this->forge->dropTable('dashboard_block_types', true);
        $this->forge->dropTable('dashboards', true);
    }
}
