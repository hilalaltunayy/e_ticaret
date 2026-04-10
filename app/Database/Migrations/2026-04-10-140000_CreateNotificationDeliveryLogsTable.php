<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationDeliveryLogsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('notification_delivery_logs')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'CHAR', 'constraint' => 36],
            'channel' => ['type' => 'VARCHAR', 'constraint' => 16],
            'recipient_email' => ['type' => 'VARCHAR', 'constraint' => 160],
            'subject' => ['type' => 'VARCHAR', 'constraint' => 180],
            'template_type' => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true],
            'source_type' => ['type' => 'VARCHAR', 'constraint' => 16],
            'status' => ['type' => 'VARCHAR', 'constraint' => 16],
            'error_message' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'sent_at' => ['type' => 'DATETIME', 'null' => true],
            'created_by' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('channel', false, false, 'idx_notification_delivery_logs_channel');
        $this->forge->addKey('status', false, false, 'idx_notification_delivery_logs_status');
        $this->forge->addKey('source_type', false, false, 'idx_notification_delivery_logs_source_type');
        $this->forge->addKey('sent_at', false, false, 'idx_notification_delivery_logs_sent_at');
        $this->forge->createTable('notification_delivery_logs', true);
    }

    public function down()
    {
        $this->forge->dropTable('notification_delivery_logs', true);
    }
}
