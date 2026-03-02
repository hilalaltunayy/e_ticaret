<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInvoicesTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('invoices')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'CHAR', 'constraint' => 36],
            'order_id' => ['type' => 'CHAR', 'constraint' => 36, 'null' => false],
            'invoice_no' => ['type' => 'VARCHAR', 'constraint' => 24, 'null' => false],
            'series' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'generated'],
            'currency' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'TRY'],
            'subtotal' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'tax_total' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'grand_total' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'vat_rate' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.20],
            'ubl_xml_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'pdf_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('order_id', 'uq_invoices_order_id');
        $this->forge->addUniqueKey('invoice_no', 'uq_invoices_invoice_no');
        $this->forge->addKey('created_at');
        $this->forge->createTable('invoices', true);

        try {
            $this->db->query(
                'ALTER TABLE invoices
                 ADD CONSTRAINT fk_invoices_order
                 FOREIGN KEY (order_id) REFERENCES orders(id)
                 ON DELETE CASCADE ON UPDATE CASCADE'
            );
        } catch (\Throwable $e) {
            // FK creation is optional for backward compatibility.
        }
    }

    public function down()
    {
        if ($this->db->tableExists('invoices')) {
            $this->forge->dropTable('invoices', true);
        }
    }
}
