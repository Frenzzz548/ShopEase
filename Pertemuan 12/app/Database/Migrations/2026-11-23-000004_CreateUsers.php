<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class CreateUsers extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('users')) {
            throw new RuntimeException('Tabel users sudah ada. Periksa skema dan riwayat migration.');
        }

        $this->forge->addField([
            'id_user'    => ['type' => 'INT', 'auto_increment' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'email'      => ['type' => 'VARCHAR', 'constraint' => 100],
            'password'   => ['type' => 'VARCHAR', 'constraint' => 255],
            'role'       => ['type' => 'ENUM', 'constraint' => ['customer', 'admin', 'operator'], 'default' => 'customer'],
            'phone'      => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'address'    => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id_user');
        $this->forge->addUniqueKey('email', 'uq_users_email');

        if (! $this->forge->createTable('users', false, ['ENGINE' => 'InnoDB'])) {
            throw new RuntimeException('Tabel users gagal dibuat.');
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('users', true);
    }
}
