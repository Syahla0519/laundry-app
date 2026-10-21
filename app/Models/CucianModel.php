<?php

namespace App\Models;

use CodeIgniter\Model;

class CucianModel extends Model
{
    protected $table         = 'cucian';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'pelanggan_id', 'layanan_id', 'berat_kg', 'total_harga', 'status', 'tanggal_masuk',
        'dicatat_oleh', 'diselesaikan_oleh', 'tanggal_selesai',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'pelanggan_id'  => 'required|is_natural_no_zero',
        'layanan_id'    => 'required|is_natural_no_zero',
        'berat_kg'      => 'required|numeric|greater_than[0]',
        'tanggal_masuk' => 'required|valid_date',
    ];

    protected $validationMessages = [
        'pelanggan_id'  => ['required' => 'Pelanggan wajib dipilih'],
        'layanan_id'    => ['required' => 'Layanan wajib dipilih'],
        'berat_kg'      => ['required' => 'Berat pakaian wajib diisi', 'greater_than' => 'Berat harus lebih dari 0'],
        'tanggal_masuk' => ['required' => 'Tanggal masuk wajib diisi'],
    ];

    /** Ambil semua cucian dengan join nama pelanggan, layanan, & pegawai (pencatat/penyelesai) */
    public function getAllWithRelasi()
    {
        return $this->select('cucian.*, pelanggan.nama as nama_pelanggan, layanan.nama_layanan, layanan.harga_per_kg,
                               u1.nama as nama_pencatat, u2.nama as nama_penyelesai')
            ->join('pelanggan', 'pelanggan.id = cucian.pelanggan_id')
            ->join('layanan', 'layanan.id = cucian.layanan_id')
            ->join('users u1', 'u1.id = cucian.dicatat_oleh', 'left')
            ->join('users u2', 'u2.id = cucian.diselesaikan_oleh', 'left')
            ->orderBy('cucian.tanggal_masuk', 'DESC')
            ->orderBy('cucian.id', 'DESC')
            ->findAll();
    }

    /** Satu cucian lengkap dengan nama pelanggan & layanan (untuk keterangan log) */
    public function findDenganNama(int $id)
    {
        return $this->select('cucian.*, pelanggan.nama as nama_pelanggan, layanan.nama_layanan')
            ->join('pelanggan', 'pelanggan.id = cucian.pelanggan_id')
            ->join('layanan', 'layanan.id = cucian.layanan_id')
            ->where('cucian.id', $id)
            ->first();
    }

    /** Semua cucian milik satu pelanggan (dipakai dashboard role "user") */
    public function untukPelanggan(int $pelangganId): array
    {
        return $this->select('cucian.*, layanan.nama_layanan, u1.nama as nama_pencatat, u2.nama as nama_penyelesai')
            ->join('layanan', 'layanan.id = cucian.layanan_id')
            ->join('users u1', 'u1.id = cucian.dicatat_oleh', 'left')
            ->join('users u2', 'u2.id = cucian.diselesaikan_oleh', 'left')
            ->where('cucian.pelanggan_id', $pelangganId)
            ->orderBy('cucian.tanggal_masuk', 'DESC')
            ->orderBy('cucian.id', 'DESC')
            ->findAll();
    }

    /** Jumlah cucian yang masih dikerjakan (belum selesai) */
    public function jumlahDiproses()
    {
        return $this->whereIn('status', ['diterima', 'diproses'])->countAllResults();
    }

    /** Total pendapatan hari ini (transaksi yang sudah diambil = final) */
    public function pendapatanHariIni()
    {
        return $this->where('DATE(tanggal_masuk)', date('Y-m-d'))->selectSum('total_harga')->first()['total_harga'] ?? 0;
    }

    /** Total pendapatan bulan berjalan (1 s/d hari ini) */
    public function pendapatanBulanIni()
    {
        return $this->where('tanggal_masuk >=', date('Y-m-01'))
            ->where('tanggal_masuk <=', date('Y-m-d'))
            ->selectSum('total_harga')
            ->first()['total_harga'] ?? 0;
    }

    /** Rekap pendapatan per tanggal, untuk chart & tabel */
    public function rekapPendapatanHarian(string $mulai, string $sampai)
    {
        return $this->select('tanggal_masuk, SUM(total_harga) as total, COUNT(id) as jumlah_transaksi')
            ->where('tanggal_masuk >=', $mulai)
            ->where('tanggal_masuk <=', $sampai)
            ->groupBy('tanggal_masuk')
            ->orderBy('tanggal_masuk', 'ASC')
            ->findAll();
    }

    /** Layanan paling banyak digunakan */
    public function layananTerpopuler()
    {
        return $this->select('layanan.nama_layanan, COUNT(cucian.id) as jumlah')
            ->join('layanan', 'layanan.id = cucian.layanan_id')
            ->groupBy('cucian.layanan_id')
            ->orderBy('jumlah', 'DESC')
            ->findAll();
    }
}
