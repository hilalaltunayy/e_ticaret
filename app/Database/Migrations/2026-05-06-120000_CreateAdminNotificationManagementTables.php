<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdminNotificationManagementTables extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('admin_notification_preferences')) {
            $this->forge->addField([
                'id' => ['type' => 'CHAR', 'constraint' => 36],
                'user_id' => ['type' => 'CHAR', 'constraint' => 36],
                'user_role' => ['type' => 'VARCHAR', 'constraint' => 24],
                'notification_key' => ['type' => 'VARCHAR', 'constraint' => 80],
                'category_key' => ['type' => 'VARCHAR', 'constraint' => 32],
                'is_enabled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'threshold_value' => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey('user_id', false, false, 'idx_admin_notification_preferences_user');
            $this->forge->addKey(['user_id', 'notification_key'], true);
            $this->forge->createTable('admin_notification_preferences', true);
        }

        if (! $this->db->tableExists('admin_notification_records')) {
            $this->forge->addField([
                'id' => ['type' => 'CHAR', 'constraint' => 36],
                'user_id' => ['type' => 'CHAR', 'constraint' => 36],
                'notification_key' => ['type' => 'VARCHAR', 'constraint' => 80],
                'category_key' => ['type' => 'VARCHAR', 'constraint' => 32],
                'severity' => ['type' => 'VARCHAR', 'constraint' => 16],
                'title' => ['type' => 'VARCHAR', 'constraint' => 180],
                'message' => ['type' => 'TEXT'],
                'status' => ['type' => 'VARCHAR', 'constraint' => 16, 'default' => 'active'],
                'source_type' => ['type' => 'VARCHAR', 'constraint' => 32, 'default' => 'system_preview'],
                'fingerprint' => ['type' => 'VARCHAR', 'constraint' => 64],
                'context_json' => ['type' => 'TEXT', 'null' => true],
                'resolved_at' => ['type' => 'DATETIME', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey('user_id', false, false, 'idx_admin_notification_records_user');
            $this->forge->addKey('status', false, false, 'idx_admin_notification_records_status');
            $this->forge->addKey(['user_id', 'fingerprint'], true);
            $this->forge->createTable('admin_notification_records', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('admin_notification_records', true);
        $this->forge->dropTable('admin_notification_preferences', true);
    }
}
