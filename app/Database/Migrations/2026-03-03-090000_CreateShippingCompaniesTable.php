<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShippingCompaniesTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('shipping_companies')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => false,
            ],
            'integration_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'Yok',
                'null' => false,
            ],
            'note' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
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
        $this->forge->addKey('name', false, false, 'idx_shipping_companies_name');
        $this->forge->addKey('is_active', false, false, 'idx_shipping_companies_is_active');
        $this->forge->createTable('shipping_companies', true);
    }

    public function down()
    {
        if ($this->db->tableExists('shipping_companies')) {
            $this->forge->dropTable('shipping_companies', true);
        }
    }
}
