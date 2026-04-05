<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBlockInstancesTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('block_instances')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'CHAR', 'constraint' => 36],
            'owner_type' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'PAGE'],
            'owner_version_id' => ['type' => 'CHAR', 'constraint' => 36],
            'block_type_id' => ['type' => 'CHAR', 'constraint' => 36],
            'zone' => ['type' => 'VARCHAR', 'constraint' => 64, 'default' => 'main'],
            'position_x' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'position_y' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'width' => ['type' => 'INT', 'constraint' => 11, 'default' => 12],
            'height' => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'order_index' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'config_json' => ['type' => 'TEXT', 'null' => true],
            'is_visible' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('owner_type', false, false, 'idx_block_instances_owner_type');
        $this->forge->addKey('owner_version_id', false, false, 'idx_block_instances_owner_version_id');
        $this->forge->addKey('block_type_id', false, false, 'idx_block_instances_block_type_id');
        $this->forge->addKey('order_index', false, false, 'idx_block_instances_order_index');
        $this->forge->addKey('is_visible', false, false, 'idx_block_instances_is_visible');
        $this->forge->createTable('block_instances', true);

        if ($this->db->tableExists('block_instances') && $this->db->tableExists('page_versions')) {
            try {
                $this->db->query("ALTER TABLE block_instances
                    ADD CONSTRAINT fk_block_instances_owner_version
                    FOREIGN KEY (owner_version_id) REFERENCES page_versions(id)
                    ON DELETE CASCADE ON UPDATE CASCADE");
            } catch (\Throwable $e) {
            }
        }

        if ($this->db->tableExists('block_instances') && $this->db->tableExists('block_types')) {
            try {
                $this->db->query("ALTER TABLE block_instances
                    ADD CONSTRAINT fk_block_instances_block_type
                    FOREIGN KEY (block_type_id) REFERENCES block_types(id)
                    ON DELETE RESTRICT ON UPDATE CASCADE");
            } catch (\Throwable $e) {
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('block_instances', true);
    }
}
