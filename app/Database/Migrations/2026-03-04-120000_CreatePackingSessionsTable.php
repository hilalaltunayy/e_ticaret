<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePackingSessionsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('packing_sessions')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'CHAR', 'constraint' => 36],
            'order_id' => ['type' => 'CHAR', 'constraint' => 36, 'null' => false],
            'package_code' => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => false],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'open', 'null' => false],
            'expected_items_json' => ['type' => 'LONGTEXT', 'null' => true],
            'scanned_items_json' => ['type' => 'LONGTEXT', 'null' => true],
            'verified_at' => ['type' => 'DATETIME', 'null' => true],
            'created_by' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('package_code', 'uq_packing_sessions_package_code');
        $this->forge->addKey('order_id', false, false, 'idx_packing_sessions_order_id');
        $this->forge->addKey('status', false, false, 'idx_packing_sessions_status');
        $this->forge->createTable('packing_sessions', true);

        try {
            $this->db->query(
                'ALTER TABLE packing_sessions
                 ADD CONSTRAINT fk_packing_sessions_order
                 FOREIGN KEY (order_id) REFERENCES orders(id)
                 ON DELETE CASCADE ON UPDATE CASCADE'
            );
        } catch (\Throwable $e) {
            // FK creation is optional for backward compatibility.
        }
    }

    public function down()
    {
        if ($this->db->tableExists('packing_sessions')) {
            $this->forge->dropTable('packing_sessions', true);
        }
    }
}
