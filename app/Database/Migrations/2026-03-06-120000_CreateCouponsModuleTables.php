<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCouponsModuleTables extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('coupons')) {
            $this->forge->addField([
                'id' => ['type' => 'CHAR', 'constraint' => 36],
                'code' => ['type' => 'VARCHAR', 'constraint' => 64],
                'coupon_kind' => ['type' => 'VARCHAR', 'constraint' => 32, 'default' => 'discount'],
                'discount_type' => ['type' => 'VARCHAR', 'constraint' => 16, 'default' => 'none'],
                'discount_value' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true],
                'min_cart_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true],
                'max_usage_total' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'max_usage_per_user' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'starts_at' => ['type' => 'DATETIME', 'null' => true],
                'ends_at' => ['type' => 'DATETIME', 'null' => true],
                'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'is_first_order_only' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'created_by' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
                'updated_by' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
                'deleted_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('code', 'uq_coupons_code');
            $this->forge->addKey('is_active', false, false, 'idx_coupons_is_active');
            $this->forge->addKey('coupon_kind', false, false, 'idx_coupons_kind');
            $this->forge->addKey('starts_at', false, false, 'idx_coupons_starts_at');
            $this->forge->addKey('ends_at', false, false, 'idx_coupons_ends_at');
            $this->forge->createTable('coupons', true);
        }

        if (! $this->db->tableExists('coupon_targets')) {
            $this->forge->addField([
                'id' => ['type' => 'CHAR', 'constraint' => 36],
                'coupon_id' => ['type' => 'CHAR', 'constraint' => 36],
                'target_type' => ['type' => 'VARCHAR', 'constraint' => 16],
                'target_id' => ['type' => 'CHAR', 'constraint' => 36],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('coupon_id', false, false, 'idx_coupon_targets_coupon_id');
            $this->forge->addKey('target_type', false, false, 'idx_coupon_targets_type');
            $this->forge->addKey(['coupon_id', 'target_type', 'target_id'], false, true, 'uq_coupon_target_pair');
            $this->forge->createTable('coupon_targets', true);
        }

        if (! $this->db->tableExists('coupon_redemptions')) {
            $this->forge->addField([
                'id' => ['type' => 'CHAR', 'constraint' => 36],
                'coupon_id' => ['type' => 'CHAR', 'constraint' => 36],
                'user_id' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
                'order_id' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
                'coupon_code_snapshot' => ['type' => 'VARCHAR', 'constraint' => 64],
                'discount_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('coupon_id', false, false, 'idx_coupon_redemptions_coupon_id');
            $this->forge->addKey('user_id', false, false, 'idx_coupon_redemptions_user_id');
            $this->forge->addKey('order_id', false, false, 'idx_coupon_redemptions_order_id');
            $this->forge->createTable('coupon_redemptions', true);
        }

        if ($this->db->tableExists('coupon_targets')) {
            try {
                $this->db->query("ALTER TABLE coupon_targets
                    ADD CONSTRAINT fk_coupon_targets_coupon
                    FOREIGN KEY (coupon_id) REFERENCES coupons(id)
                    ON DELETE CASCADE ON UPDATE CASCADE");
            } catch (\Throwable $e) {
            }
        }

        if ($this->db->tableExists('coupon_redemptions')) {
            try {
                $this->db->query("ALTER TABLE coupon_redemptions
                    ADD CONSTRAINT fk_coupon_redemptions_coupon
                    FOREIGN KEY (coupon_id) REFERENCES coupons(id)
                    ON DELETE CASCADE ON UPDATE CASCADE");
            } catch (\Throwable $e) {
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('coupon_redemptions', true);
        $this->forge->dropTable('coupon_targets', true);
        $this->forge->dropTable('coupons', true);
    }
}

