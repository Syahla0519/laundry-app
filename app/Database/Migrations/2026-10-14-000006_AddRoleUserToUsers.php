<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Role ke-3: "user" (pelanggan). Akun user ditautkan ke satu data pelanggan
 * lewat kolom users.pelanggan_id, supaya dia hanya bisa melihat cuciannya sendiri.
 */
class AddRoleUserToUsers extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('users', [
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['pegawai', 'pemilik', 'user'],
                'default'    => 'pegawai',
            ],
        ]);

        $this->forge->addColumn('users', [
            'pelanggan_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'role',
            ],
        ]);

        // forge->addForeignKey() hanya jalan saat createTable(), jadi FK dibuat lewat query langsung
        $this->db->query('ALTER TABLE users ADD UNIQUE KEY users_pelanggan_id_unique (pelanggan_id)');
        $this->db->query('ALTER TABLE users ADD CONSTRAINT users_pelanggan_id_foreign
            FOREIGN KEY (pelanggan_id) REFERENCES pelanggan (id) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down()
    {
        $ada = $this->db->query("SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'
            AND CONSTRAINT_NAME = 'users_pelanggan_id_foreign'")->getNumRows();
        if ($ada) {
            $this->db->query('ALTER TABLE users DROP FOREIGN KEY users_pelanggan_id_foreign');
        }
        $this->db->query('ALTER TABLE users DROP INDEX users_pelanggan_id_unique');
        $this->forge->dropColumn('users', 'pelanggan_id');

        $this->db->table('users')->where('role', 'user')->delete();
        $this->forge->modifyColumn('users', [
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['pegawai', 'pemilik'],
                'default'    => 'pegawai',
            ],
        ]);
    }
}
