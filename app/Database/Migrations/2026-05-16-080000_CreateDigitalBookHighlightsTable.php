<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDigitalBookHighlightsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('digital_book_highlights')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'CHAR',
                'constraint' => 36,
            ],
            'user_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'null' => false,
            ],
            'product_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'null' => false,
            ],
            'page_no' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'selected_text' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'start_offset' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'end_offset' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'color' => [
                'type' => 'VARCHAR',
                'constraint' => 32,
                'default' => 'yellow',
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
        $this->forge->addKey(['user_id', 'product_id']);
        $this->forge->addKey(['product_id', 'page_no']);
        $this->forge->createTable('digital_book_highlights');
    }

    public function down()
    {
        if ($this->db->tableExists('digital_book_highlights')) {
            $this->forge->dropTable('digital_book_highlights');
        }
    }
}
