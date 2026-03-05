<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddShippedAtToOrdersIfMissing extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('orders')) {
            return;
        }

        if (! $this->db->fieldExists('shipped_at', 'orders')) {
            $this->forge->addColumn('orders', [
                'shipped_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
        }

        $fields = array_map(
            static fn ($field): string => strtolower((string) $field),
            $this->db->getFieldNames('orders')
        );

        if (! in_array('shipped_at', $fields, true) || ! in_array('updated_at', $fields, true)) {
            return;
        }

        if (in_array('shipping_status', $fields, true)) {
            $this->db->query(
                "UPDATE orders
                 SET shipped_at = updated_at
                 WHERE shipped_at IS NULL
                   AND updated_at IS NOT NULL
                   AND shipping_status IN ('shipped', 'delivered', 'delayed', 'cancelled', 'returned')"
            );
        } elseif (in_array('delivered_at', $fields, true)) {
            $this->db->query(
                "UPDATE orders
                 SET shipped_at = updated_at
                 WHERE shipped_at IS NULL
                   AND updated_at IS NOT NULL
                   AND delivered_at IS NOT NULL"
            );
        }
    }

    public function down()
    {
        if ($this->db->tableExists('orders') && $this->db->fieldExists('shipped_at', 'orders')) {
            $this->forge->dropColumn('orders', 'shipped_at');
        }
    }
}
