<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductReviewsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('product_reviews')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'CHAR', 'constraint' => 36],
            'product_id' => ['type' => 'CHAR', 'constraint' => 36, 'null' => false],
            'user_id' => ['type' => 'CHAR', 'constraint' => 36, 'null' => false],
            'order_id' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'order_item_id' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'rating' => ['type' => 'TINYINT', 'constraint' => 3, 'null' => false],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'comment' => ['type' => 'TEXT', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'pending', 'null' => false],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('product_id', false, false, 'idx_product_reviews_product_id');
        $this->forge->addKey('user_id', false, false, 'idx_product_reviews_user_id');
        $this->forge->addKey('status', false, false, 'idx_product_reviews_status');
        $this->forge->addKey(['product_id', 'status'], false, false, 'idx_product_reviews_product_status');
        $this->forge->addKey(['user_id', 'product_id'], false, false, 'idx_product_reviews_user_product');
        $this->forge->createTable('product_reviews', true);

        $this->tryAddForeignKey(
            'product_reviews',
            'fk_product_reviews_product',
            'product_id',
            'products',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->tryAddForeignKey(
            'product_reviews',
            'fk_product_reviews_user',
            'user_id',
            'users',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->tryAddForeignKey(
            'product_reviews',
            'fk_product_reviews_order',
            'order_id',
            'orders',
            'id',
            'SET NULL',
            'CASCADE'
        );
        $this->tryAddForeignKey(
            'product_reviews',
            'fk_product_reviews_order_item',
            'order_item_id',
            'order_items',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function down()
    {
        if ($this->db->tableExists('product_reviews')) {
            $this->forge->dropTable('product_reviews', true);
        }
    }

    private function tryAddForeignKey(
        string $table,
        string $constraintName,
        string $column,
        string $referencedTable,
        string $referencedColumn,
        string $onDelete,
        string $onUpdate
    ): void {
        try {
            $this->db->query(sprintf(
                'ALTER TABLE %s ADD CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s(%s) ON DELETE %s ON UPDATE %s',
                $table,
                $constraintName,
                $column,
                $referencedTable,
                $referencedColumn,
                $onDelete,
                $onUpdate
            ));
        } catch (\Throwable $e) {
            // Foreign keys stay optional for backward compatibility.
        }
    }
}
