<?php

namespace App\Models;

use CodeIgniter\Model;

class PelangganModel extends Model
{
    protected $table         = 'pelanggan';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['nama', 'telepon', 'alamat'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'nama'    => 'required|min_length[3]|max_length[100]',
        'telepon' => 'required|min_length[8]|max_length[20]',
        'alamat'  => 'permit_empty',
    ];

    protected $validationMessages = [
        'nama'    => ['required' => 'Nama pelanggan wajib diisi'],
        'telepon' => ['required' => 'Nomor telepon wajib diisi'],
    ];
}
