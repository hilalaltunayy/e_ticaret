<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomerCommerceFoundationTables extends Migration
{
    public function up()
    {
        $this->createFavoritesTable();
        $this->createCartsTable();
        $this->createCartItemsTable();
        $this->createPaymentsTable();
        $this->createShipmentsTable();
        $this->createShipmentEventsTable();
    }

    public function down()
    {
        if ($this->db->tableExists('shipment_events')) {
            $this->forge->dropTable('shipment_events', true);
        }
        if ($this->db->tableExists('shipments')) {
            $this->forge->dropTable('shipments', true);
        }
        if ($this->db->tableExists('payments')) {
            $this->forge->dropTable('payments', true);
        }
        if ($this->db->tableExists('cart_items')) {
            $this->forge->dropTable('cart_items', true);
        }
        if ($this->db->tableExists('carts')) {
            $this->forge->dropTable('carts', true);
        }
        if ($this->db->tableExists('favorites')) {
            $this->forge->dropTable('favorites', true);
        }
    }

    private function createFavoritesTable(): void
    {
        if ($this->db->tableExists('favorites')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'CHAR', 'constraint' => 36],
            'user_id' => ['type' => 'CHAR', 'constraint' => 36],
            'product_id' => ['type' => 'CHAR', 'constraint' => 36],
            'favorited_price' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id', false, false, 'idx_favorites_user_id');
        $this->forge->addKey('product_id', false, false, 'idx_favorites_product_id');
        $this->forge->createTable('favorites', true);
    }

    private function createCartsTable(): void
    {
        if ($this->db->tableExists('carts')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'CHAR', 'constraint' => 36],
            'user_id' => ['type' => 'CHAR', 'constraint' => 36],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'ACTIVE'],
            'currency' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'TRY'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id', false, false, 'idx_carts_user_id');
        $this->forge->addKey(['user_id', 'status'], false, false, 'idx_carts_user_status');
        $this->forge->createTable('carts', true);
    }

    private function createCartItemsTable(): void
    {
        if ($this->db->tableExists('cart_items')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'CHAR', 'constraint' => 36],
            'cart_id' => ['type' => 'CHAR', 'constraint' => 36],
            'product_id' => ['type' => 'CHAR', 'constraint' => 36],
            'quantity' => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'unit_price_snapshot' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('cart_id', false, false, 'idx_cart_items_cart_id');
        $this->forge->addKey('product_id', false, false, 'idx_cart_items_product_id');
        $this->forge->addKey(['cart_id', 'product_id'], false, false, 'idx_cart_items_cart_product');
        $this->forge->createTable('cart_items', true);
    }

    private function createPaymentsTable(): void
    {
        if ($this->db->tableExists('payments')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'CHAR', 'constraint' => 36],
            'order_id' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'provider' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'mock'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'INITIATED'],
            'amount' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'currency' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'TRY'],
            'provider_txn_id' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('order_id', false, false, 'idx_payments_order_id');
        $this->forge->createTable('payments', true);
    }

    private function createShipmentsTable(): void
    {
        if ($this->db->tableExists('shipments')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'CHAR', 'constraint' => 36],
            'order_id' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'carrier' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'tracking_number' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'PENDING'],
            'shipped_at' => ['type' => 'DATETIME', 'null' => true],
            'delivered_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('order_id', false, false, 'idx_shipments_order_id');
        $this->forge->createTable('shipments', true);
    }

    private function createShipmentEventsTable(): void
    {
        if ($this->db->tableExists('shipment_events')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'CHAR', 'constraint' => 36],
            'shipment_id' => ['type' => 'CHAR', 'constraint' => 36],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30],
            'note' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'location' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'event_time' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('shipment_id', false, false, 'idx_shipment_events_shipment_id');
        $this->forge->createTable('shipment_events', true);
    }
}
