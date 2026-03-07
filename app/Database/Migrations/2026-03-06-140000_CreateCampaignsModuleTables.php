<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCampaignsModuleTables extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('campaigns')) {
            $this->forge->addField([
                'id' => ['type' => 'CHAR', 'constraint' => 36],
                'name' => ['type' => 'VARCHAR', 'constraint' => 160],
                'slug' => ['type' => 'VARCHAR', 'constraint' => 190],
                'campaign_type' => ['type' => 'VARCHAR', 'constraint' => 32],
                'discount_type' => ['type' => 'VARCHAR', 'constraint' => 16, 'default' => 'percent'],
                'discount_value' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true],
                'min_cart_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true],
                'starts_at' => ['type' => 'DATETIME', 'null' => true],
                'ends_at' => ['type' => 'DATETIME', 'null' => true],
                'priority' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'stop_further_rules' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'created_by' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
                'updated_by' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
                'deleted_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('slug', 'uq_campaigns_slug');
            $this->forge->addKey('campaign_type', false, false, 'idx_campaigns_type');
            $this->forge->addKey('is_active', false, false, 'idx_campaigns_is_active');
            $this->forge->addKey('priority', false, false, 'idx_campaigns_priority');
            $this->forge->addKey('starts_at', false, false, 'idx_campaigns_starts_at');
            $this->forge->addKey('ends_at', false, false, 'idx_campaigns_ends_at');
            $this->forge->createTable('campaigns', true);
        }

        if (! $this->db->tableExists('campaign_targets')) {
            $this->forge->addField([
                'id' => ['type' => 'CHAR', 'constraint' => 36],
                'campaign_id' => ['type' => 'CHAR', 'constraint' => 36],
                'target_type' => ['type' => 'VARCHAR', 'constraint' => 16],
                'target_id' => ['type' => 'CHAR', 'constraint' => 36],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('campaign_id', false, false, 'idx_campaign_targets_campaign_id');
            $this->forge->addKey('target_type', false, false, 'idx_campaign_targets_target_type');
            $this->forge->addKey(['campaign_id', 'target_type', 'target_id'], false, true, 'uq_campaign_target_pair');
            $this->forge->createTable('campaign_targets', true);
        }

        if ($this->db->tableExists('campaign_targets')) {
            try {
                $this->db->query("ALTER TABLE campaign_targets
                    ADD CONSTRAINT fk_campaign_targets_campaign
                    FOREIGN KEY (campaign_id) REFERENCES campaigns(id)
                    ON DELETE CASCADE ON UPDATE CASCADE");
            } catch (\Throwable $e) {
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('campaign_targets', true);
        $this->forge->dropTable('campaigns', true);
    }
}

