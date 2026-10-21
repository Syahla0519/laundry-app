<?php

namespace App\Models;

use CodeIgniter\Model;

class PegawaiModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['nama', 'username', 'password', 'role'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'nama'     => 'required|min_length[3]',
        'username' => 'required|min_length[3]',
        'role'     => 'required|in_list[pegawai,pemilik]',
    ];

    protected $validationMessages = [
        'nama'     => ['required' => 'Nama wajib diisi'],
        'username' => ['required' => 'Username wajib diisi'],
        'role'     => ['required' => 'Role wajib dipilih'],
    ];

    /** Cek apakah username sudah dipakai akun lain (dipakai saat tambah & edit) */
    public function usernameDipakai(string $username, ?int $kecualiId = null): bool
    {
        $builder = $this->where('username', $username);
        if ($kecualiId) {
            $builder->where('id !=', $kecualiId);
        }
        return $builder->first() !== null;
    }
}
