<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\CucianModel;
use App\Models\PelangganModel;
use CodeIgniter\Controller;

class Dashboard extends Controller
{
    public function index()
    {
        $cucianModel = new CucianModel();
        $role        = session()->get('role');

        // Dashboard USER (pelanggan): hanya melihat status cuciannya sendiri
        if ($role === 'user') {
            $pelangganId = (int) session()->get('pelanggan_id');
            $pelanggan   = $pelangganId ? (new PelangganModel())->find($pelangganId) : null;
            $cucian      = $pelangganId ? $cucianModel->untukPelanggan($pelangganId) : [];

            $data = [
                'title'     => 'Status Cucian Saya',
                'pelanggan' => $pelanggan,
                'cucian'    => $cucian,
                'berjalan'  => array_values(array_filter($cucian, fn ($c) => $c['status'] !== 'selesai')),
                'jumlah'    => [
                    'total'    => count($cucian),
                    'berjalan' => count(array_filter($cucian, fn ($c) => $c['status'] !== 'selesai')),
                    'selesai'  => count(array_filter($cucian, fn ($c) => $c['status'] === 'selesai')),
                ],
            ];

            return view('dashboard/user', $data);
        }

        // Dashboard PEMILIK: fokus finansial & pengambilan keputusan
        if ($role === 'pemilik') {
            $mulai  = date('Y-m-d', strtotime('-13 days'));
            $sampai = date('Y-m-d');

            // isi hari tanpa transaksi dengan 0 supaya grafik tidak melompati tanggal
            $perTanggal = [];
            foreach ($cucianModel->rekapPendapatanHarian($mulai, $sampai) as $r) {
                $perTanggal[$r['tanggal_masuk']] = (float) $r['total'];
            }
            $rekapChart = [];
            for ($i = 13; $i >= 0; $i--) {
                $tgl          = date('Y-m-d', strtotime("-{$i} days"));
                $rekapChart[] = ['tanggal_masuk' => $tgl, 'total' => $perTanggal[$tgl] ?? 0];
            }

            $data = [
                'title'               => 'Dashboard Pemilik',
                'jumlah_diproses'     => $cucianModel->jumlahDiproses(),
                'pendapatan_hari_ini' => $cucianModel->pendapatanHariIni(),
                'pendapatan_bulan_ini'=> $cucianModel->pendapatanBulanIni(),
                'total_pelanggan'     => (new PelangganModel())->countAll(),
                'layanan_populer'     => $cucianModel->layananTerpopuler(),
                'rekap_chart'         => $rekapChart,
                'transaksi_terbaru'   => array_slice($cucianModel->getAllWithRelasi(), 0, 5),
                'stok_menipis'        => (new BarangModel())->stokMenipis(),
            ];

            return view('dashboard/pemilik', $data);
        }

        // Dashboard PEGAWAI: fokus operasional harian (penerimaan & status cucian)
        $semuaCucian = $cucianModel->getAllWithRelasi();

        $data = [
            'title'           => 'Dashboard Pegawai',
            'cucian_diterima' => array_values(array_filter($semuaCucian, fn ($c) => $c['status'] === 'diterima')),
            'cucian_diproses' => array_values(array_filter($semuaCucian, fn ($c) => $c['status'] === 'diproses')),
            'cucian_selesai'  => array_slice(array_values(array_filter($semuaCucian, fn ($c) => $c['status'] === 'selesai')), 0, 10),
            'jumlah_hari_ini' => count(array_filter($semuaCucian, fn ($c) => $c['tanggal_masuk'] === date('Y-m-d'))),
            'total_pelanggan' => (new PelangganModel())->countAll(),
            'stok_menipis'    => (new BarangModel())->stokMenipis(),
        ];

        return view('dashboard/pegawai', $data);
    }
}
