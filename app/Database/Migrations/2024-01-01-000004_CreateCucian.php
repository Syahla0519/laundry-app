<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCucian extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'pelanggan_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'layanan_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'berat_kg'       => ['type' => 'DECIMAL', 'constraint' => '8,2'],
            'total_harga'    => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'status'         => [
                'type'       => 'ENUM',
                'constraint' => ['diterima', 'diproses', 'selesai'],
                'default'    => 'diterima',
            ],
            'tanggal_masuk'  => ['type' => 'DATE'],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('pelanggan_id', 'pelanggan', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('layanan_id', 'layanan', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('cucian');
    }

    public function down()
    {
        $this->forge->dropTable('cucian');
    }
}
