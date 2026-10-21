<?php

namespace App\Models;

use CodeIgniter\Model;

class LayananModel extends Model
{
    protected $table         = 'layanan';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['nama_layanan', 'harga_per_kg'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'nama_layanan' => 'required|min_length[3]|max_length[100]',
        'harga_per_kg' => 'required|numeric|greater_than[0]',
    ];

    protected $validationMessages = [
        'nama_layanan' => ['required' => 'Nama layanan wajib diisi'],
        'harga_per_kg' => [
            'required'     => 'Harga per kg wajib diisi',
            'greater_than' => 'Harga harus lebih dari 0',
        ],
    ];
}
