<?php

namespace App\Database\Seeds;

use App\Models\UserModel;
use CodeIgniter\Database\Seeder;
use RuntimeException;
use Throwable;

class DemoUserPasswordSeeder extends Seeder
{
    public function run(): void
    {
        if ($this->db->getDatabase() !== 'db_shopease_p12') {
            throw new RuntimeException('Rotasi password demo hanya berlaku pada db_shopease_p12.');
        }

        $users = new UserModel($this->db);
        if (! $this->db->transBegin()) {
            throw new RuntimeException('Transaksi rotasi password demo tidak dapat dimulai.');
        }

        try {
            foreach (UserModel::ROLES as $role) {
                $email = $role . '.demo@shopease.test';
                $account = $users->findByEmail($email);
                if ($account === null || $account['email'] !== $email || $account['role'] !== $role) {
                    throw new RuntimeException('Akun demo tidak ditemukan atau perannya tidak sesuai: ' . $email);
                }

                $setting = 'SHOPEASE_DEMO_' . strtoupper($role) . '_PASSWORD';
                $password = env($setting);
                if (! is_string($password) || mb_strlen($password) < UserModel::MIN_PASSWORD_LENGTH || ! UserModel::canHashPassword($password)) {
                    throw new RuntimeException('Atur password demo yang valid pada ' . $setting . '.');
                }

                if (password_verify($password, $account['password'])) {
                    continue;
                }

                $updated = $users->where('email', $email)
                    ->where('role', $role)
                    ->where('password', $account['password'])
                    ->update($account['id_user'], ['password' => password_hash($password, PASSWORD_DEFAULT)]);
                if (! $updated || $this->db->affectedRows() !== 1) {
                    throw new RuntimeException('Password demo gagal diperbarui: ' . $email);
                }
            }

            if (! $this->db->transStatus() || ! $this->db->transCommit()) {
                throw new RuntimeException('Transaksi rotasi password demo gagal disimpan.');
            }
        } catch (Throwable $error) {
            $this->db->transRollback();
            throw $error;
        }
    }
}
