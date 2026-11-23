<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class CreateProductImages extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('product_images')) {
            throw new RuntimeException('Tabel product_images sudah ada. Periksa skema dan riwayat migration.');
        }

        $this->forge->addField([
            'id_image'   => ['type' => 'INT', 'auto_increment' => true],
            'id_product' => ['type' => 'INT'],
            'image'      => ['type' => 'VARCHAR', 'constraint' => 255],
            'is_primary' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id_image');
        $this->forge->addKey(['id_product', 'is_primary'], false, false, 'idx_product_images_primary');
        $this->forge->addForeignKey('id_product', 'products', 'id_product', 'CASCADE', 'CASCADE', 'fk_image_product');

        if (! $this->forge->createTable('product_images', false, ['ENGINE' => 'InnoDB'])) {
            throw new RuntimeException('Tabel product_images gagal dibuat.');
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('product_images', true);
    }
}
