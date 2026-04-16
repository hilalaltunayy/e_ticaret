<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBannersTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('banners')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'CHAR',
                'constraint' => 36,
            ],
            'banner_name' => [
                'type' => 'VARCHAR',
                'constraint' => 160,
            ],
            'banner_type' => [
                'type' => 'VARCHAR',
                'constraint' => 32,
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 180,
            ],
            'subtitle' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'image_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'button_text' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
            ],
            'button_link' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'display_order' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'created_by' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'null' => true,
            ],
            'updated_by' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('banner_type');
        $this->forge->addKey('display_order');
        $this->forge->addKey('is_active');
        $this->forge->createTable('banners');
    }

    public function down()
    {
        $this->forge->dropTable('banners', true);
    }
}
