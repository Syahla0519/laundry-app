<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLogAktivitas extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'nama_user'  => ['type' => 'VARCHAR', 'constraint' => 100],
            'role'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'aksi'       => ['type' => 'VARCHAR', 'constraint' => 30],
            'modul'      => ['type' => 'VARCHAR', 'constraint' => 30],
            'keterangan' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('created_at');
        $this->forge->addKey(['modul', 'aksi']);
        // nama_user disimpan sebagai salinan, jadi log tetap terbaca walau akunnya dihapus
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('log_aktivitas');
    }

    public function down()
    {
        $this->forge->dropTable('log_aktivitas');
    }
}
