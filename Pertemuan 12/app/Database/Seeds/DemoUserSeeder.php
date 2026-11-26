<?php

namespace App\Database\Seeds;

use App\Models\UserModel;
use CodeIgniter\Database\Seeder;
use RuntimeException;
use Throwable;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = new UserModel($this->db);
        if (! $this->db->transBegin()) {
            throw new RuntimeException('Transaksi akun demo tidak dapat dimulai.');
        }

        try {
            foreach (UserModel::ROLES as $role) {
                $email = $role . '.demo@shopease.test';
                $existing = $users->findByEmail($email);
                if ($existing !== null) {
                    if ($existing['role'] !== $role) {
                        throw new RuntimeException('Peran akun demo yang sudah ada tidak sesuai: ' . $email);
                    }

                    continue;
                }

                $setting = 'SHOPEASE_DEMO_' . strtoupper($role) . '_PASSWORD';
                $password = env($setting);
                if (! is_string($password) || mb_strlen($password) < UserModel::MIN_PASSWORD_LENGTH || ! UserModel::canHashPassword($password)) {
                    throw new RuntimeException('Atur password demo yang valid pada ' . $setting . '.');
                }

                if ($users->insert([
                    'name' => 'Demo ' . ucfirst($role),
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'role' => $role,
                ]) === false) {
                    throw new RuntimeException('Akun demo tidak dapat dibuat: ' . $email);
                }
            }

            if (! $this->db->transStatus() || ! $this->db->transCommit()) {
                throw new RuntimeException('Transaksi akun demo gagal disimpan.');
            }
        } catch (Throwable $error) {
            $this->db->transRollback();
            throw $error;
        }
    }
}
