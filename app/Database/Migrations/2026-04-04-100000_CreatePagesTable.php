<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePagesTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('pages')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'CHAR', 'constraint' => 36],
            'code' => ['type' => 'VARCHAR', 'constraint' => 64],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'ACTIVE'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code', 'uq_pages_code');
        $this->forge->addKey('status', false, false, 'idx_pages_status');
        $this->forge->createTable('pages', true);
    }

    public function down()
    {
        $this->forge->dropTable('pages', true);
    }
}
