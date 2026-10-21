<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\CucianModel;
use App\Models\LayananModel;
use App\Models\PelangganModel;
use CodeIgniter\Controller;

class Cucian extends Controller
{
    protected CucianModel $model;
    protected BarangModel $barang;

    public function __construct()
    {
        $this->model  = new CucianModel();
        $this->barang = new BarangModel();
    }

    public function index()
    {
        $data = [
            'title'      => 'Data Cucian / Transaksi',
            'pelanggan'  => (new PelangganModel())->orderBy('nama', 'ASC')->findAll(),
            'layanan'    => (new LayananModel())->orderBy('nama_layanan', 'ASC')->findAll(),
            'paket'      => $this->barang->paketSemuaLayanan(), // untuk info barang terpakai di form
        ];

        return view('cucian/index', $data);
    }

    public function data()
    {
        $rows   = $this->model->getAllWithRelasi();
        $barang = $this->barang->terpakaiPerCucian(array_column($rows, 'id'));

        foreach ($rows as &$r) {
            $r['barang_terpakai'] = $barang[$r['id']] ?? [];
        }

        return $this->response->setJSON(['data' => $rows]);
    }

    /** Teks "Plastik ×2, Label ×1" dari paket layanan, untuk keterangan log */
    private function teksPaket(int $layananId): string
    {
        $teks = array_map(fn ($p) => $p['nama_barang'] . ' ×' . $p['jumlah'], $this->barang->paketLayanan($layananId));

        return $teks ? implode(', ', $teks) : '';
    }

    /**
     * Simpan transaksi baru / update, dengan perhitungan harga otomatis.
     * Cucian baru (atau ganti layanan) otomatis memakai paket barang layanan:
     * stok dikurangi, dan ditolak bila stok tidak cukup.
     */
    public function simpan()
    {
        $id          = $this->request->getPost('id');
        $layananId   = (int) $this->request->getPost('layanan_id');
        $berat       = (float) $this->request->getPost('berat_kg');
        $status      = $this->request->getPost('status') ?: 'diterima';

        if (! in_array($status, ['diterima', 'diproses', 'selesai'], true)) {
            $status = 'diterima';
        }

        // Cucian yang sudah "selesai" terkunci: tidak boleh diedit lagi lewat form ini
        $existing = null;
        if ($id) {
            $existing = $this->model->find($id);
            if (! $existing) {
                return $this->response->setJSON(['status' => 'gagal', 'message' => 'Data tidak ditemukan.']);
            }
            if ($existing['status'] === 'selesai') {
                return $this->response->setJSON(['status' => 'gagal', 'message' => 'Cucian yang sudah selesai tidak bisa diedit lagi.']);
            }
        }

        $layanan = (new LayananModel())->find($layananId);
        if (! $layanan) {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Layanan tidak ditemukan.']);
        }

        $pelanggan = (new PelangganModel())->find($this->request->getPost('pelanggan_id'));
        if (! $pelanggan) {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Pelanggan tidak ditemukan.']);
        }

        $totalHarga = $berat * (float) $layanan['harga_per_kg'];

        $data = [
            'pelanggan_id'  => $pelanggan['id'],
            'layanan_id'    => $layananId,
            'berat_kg'      => $berat,
            'total_harga'   => $totalHarga,
            'status'        => $status,
            'tanggal_masuk' => $this->request->getPost('tanggal_masuk'),
        ];

        if (! $this->model->validate($data)) {
            return $this->response->setJSON([
                'status'  => 'gagal',
                'message' => 'Data tidak valid.',
                'errors'  => $this->model->errors(),
            ]);
        }

        $db             = $this->model->db;
        $layananBerubah = $existing && (int) $existing['layanan_id'] !== $layananId;
        $perluStok      = ! $id || $layananBerubah; // baru, atau ganti layanan -> paket barang berbeda

        $db->transBegin();

        // ganti layanan: kembalikan dulu barang paket lama, baru cek paket yang baru
        if ($layananBerubah) {
            $this->barang->kembalikanDariCucian((int) $id);
        }

        if ($perluStok) {
            $kurang = $this->barang->kekuranganStok($layananId);
            if ($kurang) {
                $db->transRollback();

                return $this->response->setJSON([
                    'status'  => 'gagal',
                    'message' => 'Stok barang tidak cukup untuk layanan "' . $layanan['nama_layanan'] . '": ' . implode(', ', $kurang) . '. Minta pemilik menambah stok.',
                ]);
            }
        }

        if ($id) {
            // Kalau lewat form ini statusnya langsung diset "selesai", catat juga siapa yang menyelesaikan
            if ($status === 'selesai') {
                $data['diselesaikan_oleh'] = session()->get('user_id');
                $data['tanggal_selesai']   = date('Y-m-d H:i:s');
            }
            $this->model->update($id, $data);
            $cucianId = (int) $id;
        } else {
            // Cucian baru: catat siapa pegawai yang menerima/mencatatnya
            $data['dicatat_oleh'] = session()->get('user_id');
            $this->model->insert($data);
            $cucianId = (int) $this->model->getInsertID();
        }

        if ($perluStok) {
            $this->barang->pakaiUntukCucian($cucianId, $layananId);
        }

        if ($db->transStatus() === false) {
            $db->transRollback();

            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal menyimpan data cucian.']);
        }
        $db->transCommit();

        $ringkas = $pelanggan['nama'] . ', ' . $layanan['nama_layanan'] . ', ' . $berat . ' kg (Rp ' . number_format($totalHarga, 0, ',', '.') . ')';
        $paket   = $perluStok ? $this->teksPaket($layananId) : '';

        if ($id) {
            log_aktivitas('ubah', 'cucian', "Mengubah cucian #{$cucianId} — {$ringkas}, status {$status}" . ($paket ? ". Barang terpakai: {$paket}" : ''));
            $pesan = 'Data cucian berhasil diperbarui.';
        } else {
            log_aktivitas('tambah', 'cucian', "Mencatat cucian #{$cucianId} — {$ringkas}" . ($paket ? ". Barang terpakai: {$paket}" : ''));
            $pesan = 'Cucian baru berhasil dicatat dengan status "' . $status . '". Total harga: Rp ' . number_format($totalHarga, 0, ',', '.')
                . ($paket ? '. Barang terpakai: ' . $paket . '.' : '');
        }

        return $this->response->setJSON(['status' => 'berhasil', 'message' => $pesan]);
    }

    public function edit($id)
    {
        $row = $this->model->find($id);

        if (! $row) {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Data tidak ditemukan.']);
        }

        // Cucian selesai terkunci: form edit tidak boleh dibuka lagi
        if ($row['status'] === 'selesai') {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Cucian yang sudah selesai terkunci. Minta pemilik untuk membuka kuncinya dulu.']);
        }

        return $this->response->setJSON(['status' => 'berhasil', 'data' => $row]);
    }

    public function hapus($id)
    {
        $row = $this->model->findDenganNama((int) $id);

        if (! $row) {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Data tidak ditemukan.']);
        }

        // Cucian yang sudah "selesai" terkunci: tidak boleh dihapus lagi
        if ($row['status'] === 'selesai') {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Cucian yang sudah selesai tidak bisa dihapus lagi.']);
        }

        $db = $this->model->db;
        $db->transBegin();
        $this->barang->kembalikanDariCucian((int) $id); // stok barang dikembalikan
        $this->model->delete($id);

        if ($db->transStatus() === false) {
            $db->transRollback();

            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal menghapus data cucian.']);
        }
        $db->transCommit();

        log_aktivitas('hapus', 'cucian', "Menghapus cucian #{$id} — {$row['nama_pelanggan']}, {$row['nama_layanan']}, {$row['berat_kg']} kg. Stok barang dikembalikan");

        return $this->response->setJSON(['status' => 'berhasil', 'message' => 'Data cucian berhasil dihapus dan stok barang dikembalikan.']);
    }

    /**
     * Update status cucian: diterima -> diproses -> selesai.
     * Begitu status jadi "selesai", otomatis catat pegawai (yang sedang login)
     * yang menyelesaikannya + waktunya. Setelah "selesai", status terkunci
     * (tidak bisa diubah-ubah lagi lewat endpoint ini).
     */
    public function updateStatus($id)
    {
        $status = $this->request->getPost('status');
        $valid  = ['diterima', 'diproses', 'selesai'];

        if (! in_array($status, $valid, true)) {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Status tidak valid.']);
        }

        $row = $this->model->findDenganNama((int) $id);
        if (! $row) {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Data tidak ditemukan.']);
        }

        if ($row['status'] === 'selesai') {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Cucian yang sudah selesai tidak bisa diubah statusnya lagi.']);
        }

        $data = ['status' => $status];

        if ($status === 'selesai') {
            $data['diselesaikan_oleh'] = session()->get('user_id');
            $data['tanggal_selesai']   = date('Y-m-d H:i:s');
        }

        $this->model->update($id, $data);

        log_aktivitas('status', 'cucian', "Mengubah status cucian #{$id} ({$row['nama_pelanggan']}, {$row['nama_layanan']}) dari {$row['status']} ke {$status}");

        return $this->response->setJSON(['status' => 'berhasil', 'message' => 'Status cucian diperbarui menjadi "' . $status . '".']);
    }

    /**
     * Buka kunci cucian "selesai" — HANYA PEMILIK (dijaga di Routes lewat filter role:pemilik).
     * Status dikembalikan ke "diproses" dan catatan penyelesai dikosongkan,
     * supaya pegawai bisa mengoreksi data lalu menyelesaikannya lagi (nama penyelesai tercatat ulang).
     */
    public function bukaKunci($id)
    {
        $row = $this->model->findDenganNama((int) $id);

        if (! $row) {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Data tidak ditemukan.']);
        }

        if ($row['status'] !== 'selesai') {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Cucian ini belum terkunci.']);
        }

        $this->model->update($id, [
            'status'            => 'diproses',
            'diselesaikan_oleh' => null,
            'tanggal_selesai'   => null,
        ]);

        log_aktivitas('buka_kunci', 'cucian', "Membuka kunci cucian #{$id} ({$row['nama_pelanggan']}, {$row['nama_layanan']}), status kembali ke diproses");

        return $this->response->setJSON(['status' => 'berhasil', 'message' => 'Kunci dibuka. Cucian dikembalikan ke status "diproses" dan bisa dikoreksi pegawai.']);
    }
}
