<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOrderManagementSchema extends Migration
{
    public function up()
    {
        $this->addOrdersColumns();
        $this->createOrderItemsTable();
        $this->createOrderLogsTable();
    }

    public function down()
    {
        if ($this->db->tableExists('order_logs')) {
            $this->forge->dropTable('order_logs', true);
        }

        if ($this->db->tableExists('order_items')) {
            $this->forge->dropTable('order_items', true);
        }

        if (! $this->db->tableExists('orders')) {
            return;
        }

        $dropColumns = [];
        foreach ([
            'order_no',
            'user_id',
            'payment_method',
            'payment_status',
            'order_status',
            'shipping_status',
            'shipping_company',
            'tracking_number',
            'shipping_address_line1',
            'shipping_address_line2',
            'shipping_city',
            'shipping_district',
            'shipping_postal_code',
            'shipping_country',
            'paid_at',
            'delivered_at',
            'return_started_at',
            'return_completed_at',
            'notes_admin',
            'updated_by',
        ] as $column) {
            if ($this->db->fieldExists($column, 'orders')) {
                $dropColumns[] = $column;
            }
        }

        if ($dropColumns !== []) {
            $this->forge->dropColumn('orders', $dropColumns);
        }
    }

    private function addOrdersColumns(): void
    {
        if (! $this->db->tableExists('orders')) {
            return;
        }

        $fields = [];

        if (! $this->db->fieldExists('order_no', 'orders')) {
            $fields['order_no'] = ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true];
        }
        if (! $this->db->fieldExists('user_id', 'orders')) {
            $fields['user_id'] = ['type' => 'CHAR', 'constraint' => 36, 'null' => true];
        }
        if (! $this->db->fieldExists('payment_method', 'orders')) {
            $fields['payment_method'] = ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'unknown', 'null' => false];
        }
        if (! $this->db->fieldExists('payment_status', 'orders')) {
            $fields['payment_status'] = ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'unpaid', 'null' => false];
        }
        if (! $this->db->fieldExists('order_status', 'orders')) {
            $fields['order_status'] = ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'pending', 'null' => false];
        }
        if (! $this->db->fieldExists('shipping_status', 'orders')) {
            $fields['shipping_status'] = ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'not_shipped', 'null' => false];
        }
        if (! $this->db->fieldExists('shipping_company', 'orders')) {
            $fields['shipping_company'] = ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true];
        }
        if (! $this->db->fieldExists('tracking_number', 'orders')) {
            $fields['tracking_number'] = ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true];
        }
        if (! $this->db->fieldExists('shipping_address_line1', 'orders')) {
            $fields['shipping_address_line1'] = ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true];
        }
        if (! $this->db->fieldExists('shipping_address_line2', 'orders')) {
            $fields['shipping_address_line2'] = ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true];
        }
        if (! $this->db->fieldExists('shipping_city', 'orders')) {
            $fields['shipping_city'] = ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true];
        }
        if (! $this->db->fieldExists('shipping_district', 'orders')) {
            $fields['shipping_district'] = ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true];
        }
        if (! $this->db->fieldExists('shipping_postal_code', 'orders')) {
            $fields['shipping_postal_code'] = ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true];
        }
        if (! $this->db->fieldExists('shipping_country', 'orders')) {
            $fields['shipping_country'] = ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true];
        }
        if (! $this->db->fieldExists('paid_at', 'orders')) {
            $fields['paid_at'] = ['type' => 'DATETIME', 'null' => true];
        }
        if (! $this->db->fieldExists('delivered_at', 'orders')) {
            $fields['delivered_at'] = ['type' => 'DATETIME', 'null' => true];
        }
        if (! $this->db->fieldExists('return_started_at', 'orders')) {
            $fields['return_started_at'] = ['type' => 'DATETIME', 'null' => true];
        }
        if (! $this->db->fieldExists('return_completed_at', 'orders')) {
            $fields['return_completed_at'] = ['type' => 'DATETIME', 'null' => true];
        }
        if (! $this->db->fieldExists('notes_admin', 'orders')) {
            $fields['notes_admin'] = ['type' => 'TEXT', 'null' => true];
        }
        if (! $this->db->fieldExists('updated_by', 'orders')) {
            $fields['updated_by'] = ['type' => 'CHAR', 'constraint' => 36, 'null' => true];
        }

        if ($fields !== []) {
            $this->forge->addColumn('orders', $fields);
        }

        $this->db->query(
            "UPDATE orders
             SET order_status = CASE
               WHEN status IN ('reserved', 'pending') THEN 'pending'
               WHEN status IN ('paid') THEN 'preparing'
               WHEN status IN ('shipped') THEN 'shipped'
               WHEN status IN ('completed') THEN 'delivered'
               WHEN status IN ('cancelled') THEN 'cancelled'
               WHEN status IN ('returned') THEN 'return_done'
               ELSE 'pending'
             END
             WHERE (order_status IS NULL OR order_status = '' OR order_status = 'pending')"
        );

        $this->db->query(
            "UPDATE orders
             SET shipping_status = CASE
               WHEN order_status IN ('shipped') THEN 'shipped'
               WHEN order_status IN ('delivered') THEN 'delivered'
               WHEN order_status IN ('return_in_progress', 'return_done') THEN 'returned'
               ELSE 'not_shipped'
             END
             WHERE (shipping_status IS NULL OR shipping_status = '' OR shipping_status = 'not_shipped')"
        );

        if (! $this->db->fieldExists('order_no', 'orders')) {
            return;
        }

        $this->db->query(
            "UPDATE orders
             SET order_no = CONCAT('ORD-', UPPER(REPLACE(SUBSTRING(id, 1, 8), '-', '')))
             WHERE order_no IS NULL OR order_no = ''"
        );

        try {
            $this->forge->addUniqueKey('order_no', 'uq_orders_order_no');
            $this->forge->processIndexes('orders');
        } catch (\Throwable $e) {
            // Unique key already exists.
        }
    }

    private function createOrderItemsTable(): void
    {
        if ($this->db->tableExists('order_items')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'CHAR', 'constraint' => 36],
            'order_id' => ['type' => 'CHAR', 'constraint' => 36, 'null' => false],
            'product_id' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'product_name_snapshot' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'unit_price' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'quantity' => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'line_total' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('order_id');
        $this->forge->addKey('product_id');
        $this->forge->createTable('order_items', true);

        try {
            $this->db->query(
                'ALTER TABLE order_items
                 ADD CONSTRAINT fk_order_items_order
                 FOREIGN KEY (order_id) REFERENCES orders(id)
                 ON DELETE CASCADE ON UPDATE CASCADE'
            );
        } catch (\Throwable $e) {
            // FK creation is optional for backward compatibility.
        }
    }

    private function createOrderLogsTable(): void
    {
        if ($this->db->tableExists('order_logs')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'CHAR', 'constraint' => 36],
            'order_id' => ['type' => 'CHAR', 'constraint' => 36, 'null' => false],
            'actor_user_id' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'actor_role' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'action' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'from_status' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'to_status' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'message' => ['type' => 'TEXT', 'null' => true],
            'meta_json' => ['type' => 'LONGTEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => false],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('order_id');
        $this->forge->addKey('actor_user_id');
        $this->forge->addKey('action');
        $this->forge->addKey('created_at');
        $this->forge->createTable('order_logs', true);

        try {
            $this->db->query(
                'ALTER TABLE order_logs
                 ADD CONSTRAINT fk_order_logs_order
                 FOREIGN KEY (order_id) REFERENCES orders(id)
                 ON DELETE CASCADE ON UPDATE CASCADE'
            );
        } catch (\Throwable $e) {
            // FK creation is optional for backward compatibility.
        }
    }
}
