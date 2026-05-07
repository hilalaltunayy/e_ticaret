<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCustomerOrdersFields extends Migration
{
    public function up()
    {
        $this->addOrdersFields();
        $this->addOrderItemsFields();
        $this->addShipmentsFields();
        $this->addShipmentEventsFields();
    }

    public function down()
    {
        if ($this->db->tableExists('shipment_events')) {
            $drop = [];
            foreach (['title'] as $field) {
                if ($this->db->fieldExists($field, 'shipment_events')) {
                    $drop[] = $field;
                }
            }
            if ($drop !== []) {
                $this->forge->dropColumn('shipment_events', $drop);
            }
        }

        if ($this->db->tableExists('shipments')) {
            $drop = [];
            foreach (['estimated_delivery_at'] as $field) {
                if ($this->db->fieldExists($field, 'shipments')) {
                    $drop[] = $field;
                }
            }
            if ($drop !== []) {
                $this->forge->dropColumn('shipments', $drop);
            }
        }

        if ($this->db->tableExists('order_items')) {
            $drop = [];
            foreach (['author', 'product_image', 'product_type', 'item_status', 'deleted_at'] as $field) {
                if ($this->db->fieldExists($field, 'order_items')) {
                    $drop[] = $field;
                }
            }
            if ($drop !== []) {
                $this->forge->dropColumn('order_items', $drop);
            }
        }

        if ($this->db->tableExists('orders')) {
            $drop = [];
            foreach (['fulfillment_status', 'item_count', 'subtotal_amount', 'shipping_amount', 'discount_amount', 'currency', 'estimated_delivery_at'] as $field) {
                if ($this->db->fieldExists($field, 'orders')) {
                    $drop[] = $field;
                }
            }
            if ($drop !== []) {
                $this->forge->dropColumn('orders', $drop);
            }
        }
    }

    private function addOrdersFields(): void
    {
        if (! $this->db->tableExists('orders')) {
            return;
        }

        $fields = [];
        if (! $this->db->fieldExists('fulfillment_status', 'orders')) {
            $fields['fulfillment_status'] = ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'PENDING', 'null' => false];
        }
        if (! $this->db->fieldExists('item_count', 'orders')) {
            $fields['item_count'] = ['type' => 'INT', 'constraint' => 11, 'default' => 0, 'null' => false];
        }
        if (! $this->db->fieldExists('subtotal_amount', 'orders')) {
            $fields['subtotal_amount'] = ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00, 'null' => false];
        }
        if (! $this->db->fieldExists('shipping_amount', 'orders')) {
            $fields['shipping_amount'] = ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00, 'null' => false];
        }
        if (! $this->db->fieldExists('discount_amount', 'orders')) {
            $fields['discount_amount'] = ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00, 'null' => false];
        }
        if (! $this->db->fieldExists('currency', 'orders')) {
            $fields['currency'] = ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'TRY', 'null' => false];
        }
        if (! $this->db->fieldExists('estimated_delivery_at', 'orders')) {
            $fields['estimated_delivery_at'] = ['type' => 'DATETIME', 'null' => true];
        }

        if ($fields !== []) {
            $this->forge->addColumn('orders', $fields);
        }
    }

    private function addOrderItemsFields(): void
    {
        if (! $this->db->tableExists('order_items')) {
            return;
        }

        $fields = [];
        if (! $this->db->fieldExists('author', 'order_items')) {
            $fields['author'] = ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true];
        }
        if (! $this->db->fieldExists('product_image', 'order_items')) {
            $fields['product_image'] = ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true];
        }
        if (! $this->db->fieldExists('product_type', 'order_items')) {
            $fields['product_type'] = ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true];
        }
        if (! $this->db->fieldExists('item_status', 'order_items')) {
            $fields['item_status'] = ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'PREPARING', 'null' => false];
        }
        if (! $this->db->fieldExists('deleted_at', 'order_items')) {
            $fields['deleted_at'] = ['type' => 'DATETIME', 'null' => true];
        }

        if ($fields !== []) {
            $this->forge->addColumn('order_items', $fields);
        }
    }

    private function addShipmentsFields(): void
    {
        if (! $this->db->tableExists('shipments')) {
            return;
        }

        $fields = [];
        if (! $this->db->fieldExists('estimated_delivery_at', 'shipments')) {
            $fields['estimated_delivery_at'] = ['type' => 'DATETIME', 'null' => true];
        }

        if ($fields !== []) {
            $this->forge->addColumn('shipments', $fields);
        }
    }

    private function addShipmentEventsFields(): void
    {
        if (! $this->db->tableExists('shipment_events')) {
            return;
        }

        $fields = [];
        if (! $this->db->fieldExists('title', 'shipment_events')) {
            $fields['title'] = ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true];
        }

        if ($fields !== []) {
            $this->forge->addColumn('shipment_events', $fields);
        }
    }
}
