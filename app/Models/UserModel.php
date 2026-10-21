<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['nama', 'username', 'password', 'role', 'pelanggan_id'];
    protected $useTimestamps    = true;

    public function findByUsername(string $username)
    {
        return $this->where('username', $username)->first();
    }
}
