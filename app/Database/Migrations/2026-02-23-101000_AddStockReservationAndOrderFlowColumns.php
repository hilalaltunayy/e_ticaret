<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStockReservationAndOrderFlowColumns extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('reserved_count', 'products')) {
            $this->forge->addColumn('products', [
                'reserved_count' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 0,
                    'null' => false,
                    'after' => 'stock_count',
                ],
            ]);
        }

        $orderFields = [];
        if (! $this->db->fieldExists('status', 'orders')) {
            $orderFields['status'] = [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'default' => 'draft',
                'null' => false,
            ];
        }
        if (! $this->db->fieldExists('reserved_at', 'orders')) {
            $orderFields['reserved_at'] = ['type' => 'DATETIME', 'null' => true];
        }
        if (! $this->db->fieldExists('shipped_at', 'orders')) {
            $orderFields['shipped_at'] = ['type' => 'DATETIME', 'null' => true];
        }
        if (! $this->db->fieldExists('cancelled_at', 'orders')) {
            $orderFields['cancelled_at'] = ['type' => 'DATETIME', 'null' => true];
        }
        if (! $this->db->fieldExists('returned_at', 'orders')) {
            $orderFields['returned_at'] = ['type' => 'DATETIME', 'null' => true];
        }
        if (! empty($orderFields)) {
            $this->forge->addColumn('orders', $orderFields);
        }

        if (! $this->db->fieldExists('delta', 'product_stock_logs')) {
            $this->forge->addColumn('product_stock_logs', [
                'delta' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                    'after' => 'change_amount',
                ],
            ]);
            $this->db->query('UPDATE product_stock_logs SET delta = change_amount WHERE delta IS NULL');
            $this->db->query('ALTER TABLE product_stock_logs MODIFY delta INT NOT NULL');
        }

        if (! $this->db->fieldExists('note', 'product_stock_logs')) {
            $this->forge->addColumn('product_stock_logs', [
                'note' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'reason',
                ],
            ]);
        }

        if (! $this->db->fieldExists('actor_user_id', 'product_stock_logs')) {
            $this->forge->addColumn('product_stock_logs', [
                'actor_user_id' => [
                    'type' => 'CHAR',
                    'constraint' => 36,
                    'null' => true,
                    'after' => 'note',
                ],
            ]);
        }

        if (! $this->db->fieldExists('ref_no', 'product_stock_logs')) {
            $this->forge->addColumn('product_stock_logs', [
                'ref_no' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                    'after' => 'actor_user_id',
                ],
            ]);
        }

        if (! $this->db->fieldExists('related_order_id', 'product_stock_logs')) {
            $this->forge->addColumn('product_stock_logs', [
                'related_order_id' => [
                    'type' => 'CHAR',
                    'constraint' => 36,
                    'null' => true,
                    'after' => 'ref_no',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('reserved_count', 'products')) {
            $this->forge->dropColumn('products', 'reserved_count');
        }

        $dropOrders = [];
        foreach (['reserved_at', 'shipped_at', 'cancelled_at', 'returned_at'] as $column) {
            if ($this->db->fieldExists($column, 'orders')) {
                $dropOrders[] = $column;
            }
        }
        if ($dropOrders) {
            $this->forge->dropColumn('orders', $dropOrders);
        }

        $dropLogs = [];
        foreach (['delta', 'note', 'actor_user_id', 'ref_no', 'related_order_id'] as $column) {
            if ($this->db->fieldExists($column, 'product_stock_logs')) {
                $dropLogs[] = $column;
            }
        }
        if ($dropLogs) {
            $this->forge->dropColumn('product_stock_logs', $dropLogs);
        }
    }
}
