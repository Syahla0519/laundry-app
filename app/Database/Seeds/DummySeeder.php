<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Data contoh untuk demo / tugas.
 *
 *   php spark db:seed DummySeeder
 *
 * PERHATIAN: seeder ini MENGGANTI isi tabel pelanggan, layanan, barang, paket (layanan_barang),
 * cucian, cucian_barang, log_aktivitas, dan akun ber-role "user" dengan data contoh.
 * Akun pemilik & pegawai tidak dihapus. Jangan dijalankan di database yang berisi data asli.
 *
 * Isi:
 *  - 8 pelanggan (3 di antaranya punya akun login role "user", password: user123)
 *  - 4 layanan + 6 barang; tiap layanan punya paket barang (mis. "Lipat Saja" = 3 barang)
 *  - ±30 hari transaksi cucian dengan jumlah per hari naik-turun (grafik pendapatan jadi hidup)
 *  - kolom stok barang = sisa stok saat ini (sebagian sengaja dibuat menipis untuk demo peringatan stok);
 *    pemakaian barang per cucian tercatat di tabel cucian_barang
 *  - log aktivitas (login, catat cucian, ubah status) sesuai riwayat cucian
 */
class DummySeeder extends Seeder
{
    public function run()
    {
        mt_srand(2026); // hasil acak selalu sama tiap seeding

        $db  = $this->db;
        $now = date('Y-m-d H:i:s');

        // ---------- akun pemilik & pegawai ----------
        if (! $db->table('users')->where('username', 'pemilik')->countAllResults()) {
            $this->call('UserSeeder');
        }
        if (! $db->table('users')->where('username', 'pegawai2')->countAllResults()) {
            $db->table('users')->insert([
                'nama'       => 'Dewi Pegawai',
                'username'   => 'pegawai2',
                'password'   => password_hash('pegawai123', PASSWORD_DEFAULT),
                'role'       => 'pegawai',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $pemilikId  = (int) $db->table('users')->where('username', 'pemilik')->get()->getRow()->id;
        $pegawaiIds = array_map('intval', array_column(
            $db->table('users')->where('role', 'pegawai')->orderBy('id')->get()->getResultArray(),
            'id'
        ));
        $pegawaiNama = array_column($db->table('users')->get()->getResultArray(), 'nama', 'id');

        // ---------- bersihkan data lama ----------
        $db->disableForeignKeyChecks();
        foreach (['log_aktivitas', 'cucian_barang', 'cucian', 'layanan_barang', 'barang', 'layanan', 'pelanggan'] as $t) {
            $db->table($t)->truncate();
        }
        $db->table('users')->where('role', 'user')->delete();
        $db->enableForeignKeyChecks();

        // ---------- pelanggan ----------
        $pelanggan = [
            ['Safika',        '081298765432', 'Jl. Kenanga No. 12, Malang'],
            ['Syahla',        '081234567890', 'Jl. Melati No. 5, Malang'],
            ['Ibu Ratna',     '085712345678', 'Perum Bumi Arema Blok C3, Malang'],
            ['Budi Santoso',  '082134567891', 'Jl. Semeru No. 21, Malang'],
            ['Rina Wulandari','081345678912', 'Jl. Bunga Kamboja No. 8, Malang'],
            ['Andi Pratama',  '087856781234', 'Jl. Soekarno Hatta No. 77, Malang'],
            ['Maya Lestari',  '081567891234', 'Jl. Ijen No. 3, Malang'],
            ['Fajar Nugroho', '089612345678', 'Jl. Veteran No. 40, Malang'],
        ];
        foreach ($pelanggan as $p) {
            $db->table('pelanggan')->insert([
                'nama' => $p[0], 'telepon' => $p[1], 'alamat' => $p[2], 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // akun login "user" untuk 3 pelanggan pertama (username = nomor telepon, password user123)
        foreach ([1, 2, 3] as $pid) {
            $p = $pelanggan[$pid - 1];
            $db->table('users')->insert([
                'nama'         => $p[0],
                'username'     => $p[1],
                'password'     => password_hash('user123', PASSWORD_DEFAULT),
                'role'         => 'user',
                'pelanggan_id' => $pid,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }

        // ---------- layanan ----------
        $layanan = [
            1 => ['Cuci Setrika', 8000],
            2 => ['Cuci Kering',  6000],
            3 => ['Setrika Saja', 4000],
            4 => ['Lipat Saja',   3000],
        ];
        foreach ($layanan as $id => $l) {
            $db->table('layanan')->insert([
                'id' => $id, 'nama_layanan' => $l[0], 'harga_per_kg' => $l[1], 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ---------- barang: id => [nama, satuan, stok_minimum, sisa_stok_akhir_yang_diinginkan] ----------
        $barang = [
            1 => ['Deterjen',          'sachet', 20, 46],
            2 => ['Pewangi Pakaian',   'sachet', 20, 12], // sengaja menipis
            3 => ['Plastik Kemasan',   'pcs',    50, 140],
            4 => ['Label Nama',        'pcs',    30, 9],  // sengaja menipis
            5 => ['Pelicin Pakaian',   'botol',  5,  14],
            6 => ['Tali Pengikat',     'pcs',    20, 38],
        ];

        foreach ($barang as $id => $b) {
            $db->table('barang')->insert([
                'id' => $id, 'nama_barang' => $b[0], 'satuan' => $b[1],
                'stok' => $b[3], // sisa stok saat ini
                'stok_minimum' => $b[2],
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ---------- paket barang per layanan: layanan_id => [barang_id => jumlah] ----------
        $paket = [
            1 => [1 => 1, 2 => 1, 3 => 1, 4 => 1],        // Cuci Setrika
            2 => [1 => 1, 2 => 1, 3 => 1],                // Cuci Kering
            3 => [5 => 1, 3 => 1],                        // Setrika Saja
            4 => [3 => 1, 4 => 1, 6 => 1],                // Lipat Saja = 3 barang
        ];
        foreach ($paket as $lid => $items) {
            foreach ($items as $bid => $jml) {
                $db->table('layanan_barang')->insert(['layanan_id' => $lid, 'barang_id' => $bid, 'jumlah' => $jml]);
            }
        }

        // ---------- cucian 30 hari: jumlah transaksi per hari naik-turun ----------
        $pola   = [3, 5, 2, 6, 4, 1, 0, 4, 7, 3, 5, 2, 6, 8, 3, 1, 5, 6, 2, 4, 7, 3, 0, 5, 8, 4, 2, 6, 7, 4];
        $hari   = count($pola);
        $today  = new \DateTimeImmutable('today');
        $terpakai = array_fill_keys(array_keys($barang), 0);
        $logRows  = [];

        // log login harian sederhana
        $tambahLog = function (array $row) use (&$logRows) {
            $logRows[] = $row;
        };

        for ($i = $hari - 1; $i >= 0; $i--) {
            $tgl      = $today->modify("-{$i} days");
            $tglStr   = $tgl->format('Y-m-d');
            $jumlah   = $pola[$hari - 1 - $i];
            $umurHari = $i; // 0 = hari ini

            if ($jumlah > 0) {
                $pg = $pegawaiIds[array_rand($pegawaiIds)];
                $tambahLog([
                    'user_id' => $pg, 'nama_user' => $pegawaiNama[$pg], 'role' => 'pegawai',
                    'aksi' => 'login', 'modul' => 'auth', 'keterangan' => 'Login ke sistem sebagai pegawai',
                    'ip_address' => '127.0.0.1', 'created_at' => $tglStr . ' 07:' . sprintf('%02d', mt_rand(30, 59)) . ':00',
                ]);
            }

            for ($n = 0; $n < $jumlah; $n++) {
                $lid     = mt_rand(1, 10) <= 4 ? 1 : mt_rand(1, 4); // Cuci Setrika paling sering
                $pid     = mt_rand(1, count($pelanggan));
                $berat   = round(mt_rand(15, 90) / 10, 1);          // 1.5 - 9.0 kg
                $total   = $berat * $layanan[$lid][1];
                $pencatat = $pegawaiIds[array_rand($pegawaiIds)];
                $jamMasuk = sprintf('%02d:%02d:00', mt_rand(8, 17), mt_rand(0, 59));

                // status berdasarkan umur: lama = selesai, baru = campur
                if ($umurHari >= 3) {
                    $status = 'selesai';
                } elseif ($umurHari === 2) {
                    $status = mt_rand(1, 10) <= 8 ? 'selesai' : 'diproses';
                } elseif ($umurHari === 1) {
                    $status = ['selesai', 'diproses', 'diproses'][mt_rand(0, 2)];
                } else {
                    $status = ['diterima', 'diterima', 'diproses'][mt_rand(0, 2)];
                }

                // 3 cucian terbaru hari ini dipegang akun demo (pelanggan 1-3) dan masih berjalan,
                // supaya dashboard role "user" langsung menampilkan status cucian
                if ($umurHari === 0 && $n < 3) {
                    $pid    = $n + 1;
                    $status = ['diterima', 'diproses', 'diproses'][$n];
                    $total  = $berat * $layanan[$lid][1];
                }

                $penyelesai = null;
                $tglSelesai = null;
                if ($status === 'selesai') {
                    $penyelesai = $pegawaiIds[array_rand($pegawaiIds)];
                    $selesaiAt  = (new \DateTimeImmutable("$tglStr $jamMasuk"))->modify('+' . mt_rand(1, 2) . ' days +' . mt_rand(0, 3) . ' hours');
                    if ($selesaiAt > new \DateTimeImmutable('now')) {
                        $selesaiAt = new \DateTimeImmutable('now');
                    }
                    $tglSelesai = $selesaiAt->format('Y-m-d H:i:s');
                }

                $db->table('cucian')->insert([
                    'pelanggan_id'      => $pid,
                    'layanan_id'        => $lid,
                    'berat_kg'          => $berat,
                    'total_harga'       => $total,
                    'status'            => $status,
                    'dicatat_oleh'      => $pencatat,
                    'diselesaikan_oleh' => $penyelesai,
                    'tanggal_selesai'   => $tglSelesai,
                    'tanggal_masuk'     => $tglStr,
                    'created_at'        => "$tglStr $jamMasuk",
                    'updated_at'        => $tglSelesai ?? "$tglStr $jamMasuk",
                ]);
                $cucianId = (int) $db->insertID();

                // barang terpakai (snapshot paket) + akumulasi pemakaian
                $teksPaket = [];
                foreach ($paket[$lid] as $bid => $jml) {
                    $db->table('cucian_barang')->insert(['cucian_id' => $cucianId, 'barang_id' => $bid, 'jumlah' => $jml]);
                    $terpakai[$bid] += $jml;
                    $teksPaket[] = $barang[$bid][0] . ' ×' . $jml;
                }

                $ringkas = $pelanggan[$pid - 1][0] . ', ' . $layanan[$lid][0] . ', ' . $berat . ' kg (Rp ' . number_format($total, 0, ',', '.') . ')';
                $tambahLog([
                    'user_id' => $pencatat, 'nama_user' => $pegawaiNama[$pencatat], 'role' => 'pegawai',
                    'aksi' => 'tambah', 'modul' => 'cucian',
                    'keterangan' => "Mencatat cucian #{$cucianId} — {$ringkas}. Barang terpakai: " . implode(', ', $teksPaket),
                    'ip_address' => '127.0.0.1', 'created_at' => "$tglStr $jamMasuk",
                ]);
                if ($status !== 'diterima') {
                    $tambahLog([
                        'user_id' => $pencatat, 'nama_user' => $pegawaiNama[$pencatat], 'role' => 'pegawai',
                        'aksi' => 'status', 'modul' => 'cucian',
                        'keterangan' => "Mengubah status cucian #{$cucianId} ({$pelanggan[$pid - 1][0]}, {$layanan[$lid][0]}) dari diterima ke diproses",
                        'ip_address' => '127.0.0.1', 'created_at' => "$tglStr $jamMasuk",
                    ]);
                }
                if ($status === 'selesai') {
                    $tambahLog([
                        'user_id' => $penyelesai, 'nama_user' => $pegawaiNama[$penyelesai], 'role' => 'pegawai',
                        'aksi' => 'status', 'modul' => 'cucian',
                        'keterangan' => "Mengubah status cucian #{$cucianId} ({$pelanggan[$pid - 1][0]}, {$layanan[$lid][0]}) dari diproses ke selesai",
                        'ip_address' => '127.0.0.1', 'created_at' => $tglSelesai,
                    ]);
                }
            }

            // pemilik melihat laporan tiap beberapa hari
            if ($i % 5 === 0) {
                $tambahLog([
                    'user_id' => $pemilikId, 'nama_user' => $pegawaiNama[$pemilikId], 'role' => 'pemilik',
                    'aksi' => 'login', 'modul' => 'auth', 'keterangan' => 'Login ke sistem sebagai pemilik',
                    'ip_address' => '127.0.0.1', 'created_at' => $tglStr . ' 20:' . sprintf('%02d', mt_rand(0, 40)) . ':00',
                ]);
            }
        }

        // log: urut waktu lalu masukkan
        usort($logRows, fn ($a, $b) => strcmp($a['created_at'], $b['created_at']));
        foreach (array_chunk($logRows, 100) as $chunk) {
            $db->table('log_aktivitas')->insertBatch($chunk);
        }

    }
}
