<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDigitalBookContentsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('digital_book_contents')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'CHAR',
                'constraint' => 36,
            ],
            'product_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'null' => false,
            ],
            'content_text' => [
                'type' => 'LONGTEXT',
                'null' => false,
            ],
            'source_type' => [
                'type' => 'VARCHAR',
                'constraint' => 32,
                'default' => 'manual',
                'null' => false,
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
        $this->forge->addKey('product_id');
        $this->forge->createTable('digital_book_contents');
    }

    public function down()
    {
        if ($this->db->tableExists('digital_book_contents')) {
            $this->forge->dropTable('digital_book_contents');
        }
    }
}

