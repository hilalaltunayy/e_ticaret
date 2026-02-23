<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductStockLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'CHAR', 'constraint' => 36],
            'product_id' => ['type' => 'CHAR', 'constraint' => 36],
            'old_stock' => ['type' => 'INT', 'constraint' => 11],
            'new_stock' => ['type' => 'INT', 'constraint' => 11],
            'change_amount' => ['type' => 'INT', 'constraint' => 11],
            'reason' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => false],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('product_id', false, false, 'idx_stock_logs_product_id');
        $this->forge->addKey('created_at', false, false, 'idx_stock_logs_created_at');
        $this->forge->createTable('product_stock_logs', true);

        $this->db->query("ALTER TABLE product_stock_logs
            ADD CONSTRAINT fk_stock_logs_product FOREIGN KEY (product_id) REFERENCES products(id)
            ON DELETE CASCADE ON UPDATE CASCADE");
    }

    public function down()
    {
        $this->forge->dropTable('product_stock_logs', true);
    }
}
