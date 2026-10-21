<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Paket barang per layanan: 1 layanan membutuhkan N barang tiap 1 cucian. */
class CreateLayananBarang extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'layanan_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'barang_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'jumlah'     => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['layanan_id', 'barang_id']);
        $this->forge->addForeignKey('layanan_id', 'layanan', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('barang_id', 'barang', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('layanan_barang');
    }

    public function down()
    {
        $this->forge->dropTable('layanan_barang');
    }
}
