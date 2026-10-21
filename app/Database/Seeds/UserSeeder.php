<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nama'       => 'Pemilik Laundry',
                'username'   => 'pemilik',
                'password'   => password_hash('pemilik123', PASSWORD_DEFAULT),
                'role'       => 'pemilik',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama'       => 'Pegawai Laundry',
                'username'   => 'pegawai',
                'password'   => password_hash('pegawai123', PASSWORD_DEFAULT),
                'role'       => 'pegawai',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('users')->insertBatch($data);
    }
}
