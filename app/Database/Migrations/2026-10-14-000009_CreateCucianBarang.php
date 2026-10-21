<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Catatan barang yang terpakai oleh tiap cucian (snapshot saat cucian dicatat).
 * Dipakai untuk mengembalikan stok bila cucian dihapus, dan untuk menampilkan
 * barang terpakai di semua status cucian (diterima / diproses / selesai).
 */
class CreateCucianBarang extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'cucian_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'barang_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'jumlah'    => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('cucian_id', 'cucian', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('barang_id', 'barang', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('cucian_barang');
    }

    public function down()
    {
        $this->forge->dropTable('cucian_barang');
    }
}
