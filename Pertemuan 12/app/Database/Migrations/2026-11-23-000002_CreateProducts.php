<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class CreateProducts extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('products')) {
            throw new RuntimeException('Tabel products sudah ada. Periksa skema dan riwayat migration.');
        }

        $this->forge->addField([
            'id_product'  => ['type' => 'INT', 'auto_increment' => true],
            'id_category' => ['type' => 'INT'],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 150],
            'slug'        => ['type' => 'VARCHAR', 'constraint' => 180],
            'description' => ['type' => 'TEXT', 'null' => true],
            'price'       => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'stock'       => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'status'      => ['type' => 'ENUM', 'constraint' => ['tersedia', 'tidak_tersedia'], 'default' => 'tersedia'],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id_product');
        $this->forge->addUniqueKey('slug', 'uq_products_slug');
        $this->forge->addKey('id_category', false, false, 'idx_products_category');
        $this->forge->addKey(['status', 'stock'], false, false, 'idx_products_availability');
        $this->forge->addForeignKey('id_category', 'categories', 'id_category', 'CASCADE', 'RESTRICT', 'fk_product_category');

        if (! $this->forge->createTable('products', false, ['ENGINE' => 'InnoDB'])) {
            throw new RuntimeException('Tabel products gagal dibuat.');
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('products', true);
    }
}
