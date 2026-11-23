<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class CreateCategories extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('categories')) {
            throw new RuntimeException('Tabel categories sudah ada. Periksa skema dan riwayat migration.');
        }

        $this->forge->addField([
            'id_category' => ['type' => 'INT', 'auto_increment' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 100],
            'slug'        => ['type' => 'VARCHAR', 'constraint' => 120],
            'description' => ['type' => 'TEXT', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id_category');
        $this->forge->addUniqueKey('slug', 'uq_categories_slug');

        if (! $this->forge->createTable('categories', false, ['ENGINE' => 'InnoDB'])) {
            throw new RuntimeException('Tabel categories gagal dibuat.');
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('categories', true);
    }
}
