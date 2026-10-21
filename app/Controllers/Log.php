<?php

namespace App\Controllers;

use App\Models\LogModel;
use CodeIgniter\Controller;

class Log extends Controller
{
    private const PER_HALAMAN = 20;

    public function index()
    {
        $model = new LogModel();

        $filter = [
            'modul'  => (string) $this->request->getGet('modul'),
            'aksi'   => (string) $this->request->getGet('aksi'),
            'q'      => trim((string) $this->request->getGet('q')),
            'dari'   => (string) $this->request->getGet('dari'),
            'sampai' => (string) $this->request->getGet('sampai'),
        ];

        $total      = $model->filter($filter)->countAllResults();
        $jumlahHal  = max(1, (int) ceil($total / self::PER_HALAMAN));
        $halaman    = min(max(1, (int) $this->request->getGet('page')), $jumlahHal);

        $baris = $model->filter($filter)
            ->orderBy('id', 'DESC') // id naik tiap ada aktivitas baru, jadi yang terbaru selalu paling atas
            ->limit(self::PER_HALAMAN, ($halaman - 1) * self::PER_HALAMAN)
            ->get()->getResultArray();

        return view('log/index', [
            'title'      => 'Log Aktivitas',
            'baris'      => $baris,
            'filter'     => $filter,
            'total'      => $total,
            'halaman'    => $halaman,
            'jumlahHal'  => $jumlahHal,
            'perHalaman' => self::PER_HALAMAN,
            'daftarModul'=> ['auth', 'cucian', 'pelanggan', 'layanan', 'barang', 'pegawai', 'akun'],
            'daftarAksi' => ['login', 'logout', 'login_gagal', 'tambah', 'ubah', 'hapus', 'status', 'buka_kunci', 'restok', 'buat_akun', 'reset_password', 'ubah_akun'],
        ]);
    }
}
