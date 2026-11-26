<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    public const ROLES = ['customer', 'admin', 'operator'];
    public const MIN_PASSWORD_LENGTH = 8;
    public const MAX_PASSWORD_BYTES = 72;

    protected $table = 'users';
    protected $primaryKey = 'id_user';
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['name', 'email', 'password', 'role', 'phone', 'address'];
    protected $useTimestamps = true;

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    public static function canHashPassword(string $password): bool
    {
        return strlen($password) <= self::MAX_PASSWORD_BYTES && ! str_contains($password, "\0");
    }
}
