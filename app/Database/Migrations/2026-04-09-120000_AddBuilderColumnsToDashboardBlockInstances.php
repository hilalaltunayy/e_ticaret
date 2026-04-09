<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBuilderColumnsToDashboardBlockInstances extends Migration
{
    public function up()
    {
        $table = 'dashboard_block_instances';

        if (! $this->db->tableExists($table)) {
            return;
        }

        $columns = [];

        if (! $this->db->fieldExists('title', $table)) {
            $columns['title'] = [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ];
        }

        if (! $this->db->fieldExists('width', $table)) {
            $columns['width'] = [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 4,
            ];
        }

        if (! $this->db->fieldExists('height', $table)) {
            $columns['height'] = [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 1,
            ];
        }

        if (! $this->db->fieldExists('is_visible', $table)) {
            $columns['is_visible'] = [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ];
        }

        if ($columns !== []) {
            $this->forge->addColumn($table, $columns);
        }
    }

    public function down()
    {
        $table = 'dashboard_block_instances';

        if (! $this->db->tableExists($table)) {
            return;
        }

        foreach (['title', 'width', 'height', 'is_visible'] as $column) {
            if ($this->db->fieldExists($column, $table)) {
                $this->forge->dropColumn($table, $column);
            }
        }
    }
}
