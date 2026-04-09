<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMissingColumnsToDashboardBlockInstances extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('dashboard_block_instances')) {
            return;
        }

        $fields = [];

        if (! $this->db->fieldExists('title', 'dashboard_block_instances')) {
            $fields['title'] = [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ];
        }

        if (! $this->db->fieldExists('width', 'dashboard_block_instances')) {
            $fields['width'] = [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 4,
            ];
        }

        if (! $this->db->fieldExists('height', 'dashboard_block_instances')) {
            $fields['height'] = [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 1,
            ];
        }

        if (! $this->db->fieldExists('is_visible', 'dashboard_block_instances')) {
            $fields['is_visible'] = [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('dashboard_block_instances', $fields);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('dashboard_block_instances')) {
            return;
        }

        $drop = [];

        foreach (['title', 'width', 'height', 'is_visible'] as $field) {
            if ($this->db->fieldExists($field, 'dashboard_block_instances')) {
                $drop[] = $field;
            }
        }

        if ($drop !== []) {
            $this->forge->dropColumn('dashboard_block_instances', $drop);
        }
    }
}