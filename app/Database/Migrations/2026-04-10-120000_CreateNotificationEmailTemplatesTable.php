<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationEmailTemplatesTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('notification_email_templates')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'CHAR', 'constraint' => 36],
            'template_name' => ['type' => 'VARCHAR', 'constraint' => 160],
            'template_type' => ['type' => 'VARCHAR', 'constraint' => 32],
            'subject' => ['type' => 'VARCHAR', 'constraint' => 180],
            'message' => ['type' => 'TEXT'],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_by' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'updated_by' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('template_type', false, false, 'idx_notification_email_templates_type');
        $this->forge->addKey('is_active', false, false, 'idx_notification_email_templates_is_active');
        $this->forge->addKey('updated_at', false, false, 'idx_notification_email_templates_updated_at');
        $this->forge->createTable('notification_email_templates', true);
    }

    public function down()
    {
        $this->forge->dropTable('notification_email_templates', true);
    }
}
