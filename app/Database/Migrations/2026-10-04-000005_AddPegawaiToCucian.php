<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPegawaiToCucian extends Migration
{
    public function up()
    {
        $this->forge->addColumn('cucian', [
            'dicatat_oleh' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'status',
            ],
            'diselesaikan_oleh' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'dicatat_oleh',
            ],
            'tanggal_selesai' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'diselesaikan_oleh',
            ],
        ]);

        $this->forge->addForeignKey('dicatat_oleh', 'users', 'id', '', 'SET NULL', '', 'cucian');
        $this->forge->addForeignKey('diselesaikan_oleh', 'users', 'id', '', 'SET NULL', '', 'cucian');
    }

    public function down()
    {
        $this->forge->dropForeignKey('cucian', 'cucian_dicatat_oleh_foreign');
        $this->forge->dropForeignKey('cucian', 'cucian_diselesaikan_oleh_foreign');
        $this->forge->dropColumn('cucian', ['dicatat_oleh', 'diselesaikan_oleh', 'tanggal_selesai']);
    }
}
