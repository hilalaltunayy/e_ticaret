<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePageVersionsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('page_versions')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'CHAR', 'constraint' => 36],
            'page_id' => ['type' => 'CHAR', 'constraint' => 36],
            'version_no' => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'DRAFT'],
            'created_by' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'published_at' => ['type' => 'DATETIME', 'null' => true],
            'scheduled_publish_at' => ['type' => 'DATETIME', 'null' => true],
            'archived_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('page_id', false, false, 'idx_page_versions_page_id');
        $this->forge->addKey('status', false, false, 'idx_page_versions_status');
        $this->forge->addKey(['page_id', 'version_no'], false, true, 'uq_page_versions_page_version_no');
        $this->forge->createTable('page_versions', true);

        if ($this->db->tableExists('page_versions') && $this->db->tableExists('pages')) {
            try {
                $this->db->query("ALTER TABLE page_versions
                    ADD CONSTRAINT fk_page_versions_page
                    FOREIGN KEY (page_id) REFERENCES pages(id)
                    ON DELETE CASCADE ON UPDATE CASCADE");
            } catch (\Throwable $e) {
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('page_versions', true);
    }
}
