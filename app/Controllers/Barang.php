<?php

namespace App\Controllers;

use App\Models\BarangModel;
use CodeIgniter\Controller;

class Barang extends Controller
{
    protected BarangModel $model;

    public function __construct()
    {
        $this->model = new BarangModel();
    }

    public function index()
    {
        return view('barang/index', ['title' => 'Stok Barang']);
    }

    public function data()
    {
        return $this->response->setJSON(['data' => $this->model->semuaDenganPemakaian()]);
    }

    public function simpan()
    {
        $id   = $this->request->getPost('id');
        $data = [
            'nama_barang'  => trim((string) $this->request->getPost('nama_barang')),
            'satuan'       => trim((string) $this->request->getPost('satuan')),
            'stok'         => $this->request->getPost('stok'),
            'stok_minimum' => $this->request->getPost('stok_minimum'),
        ];

        if (! $this->model->validate($data)) {
            return $this->response->setJSON([
                'status'  => 'gagal',
                'message' => 'Data tidak valid.',
                'errors'  => $this->model->errors(),
            ]);
        }

        $kembar = $this->model->where('LOWER(nama_barang)', mb_strtolower($data['nama_barang']));
        if ($id) {
            $kembar->where('id !=', $id);
        }
        if ($kembar->first()) {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Nama barang sudah ada.']);
        }

        if ($id) {
            $lama = $this->model->find($id);
            if (! $lama) {
                return $this->response->setJSON(['status' => 'gagal', 'message' => 'Data tidak ditemukan.']);
            }
            $this->model->update($id, $data);
            log_aktivitas('ubah', 'barang', "Mengubah barang {$data['nama_barang']}: stok {$lama['stok']} → {$data['stok']} {$data['satuan']}, batas minimum {$data['stok_minimum']}");
            $pesan = 'Data barang berhasil diperbarui.';
        } else {
            $this->model->insert($data);
            log_aktivitas('tambah', 'barang', "Menambah barang {$data['nama_barang']} dengan stok awal {$data['stok']} {$data['satuan']}");
            $pesan = 'Barang baru berhasil ditambahkan.';
        }

        return $this->response->setJSON(['status' => 'berhasil', 'message' => $pesan]);
    }

    /** Tambah stok (restok) tanpa harus membuka form edit */
    public function tambahStok($id)
    {
        $row    = $this->model->find($id);
        $jumlah = (int) $this->request->getPost('jumlah');

        if (! $row) {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Data tidak ditemukan.']);
        }
        if ($jumlah < 1) {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Jumlah restok minimal 1.']);
        }

        $this->model->db->query('UPDATE barang SET stok = stok + ?, updated_at = NOW() WHERE id = ?', [$jumlah, (int) $id]);
        $baru = (int) $row['stok'] + $jumlah;

        log_aktivitas('restok', 'barang', "Restok {$row['nama_barang']} +{$jumlah} {$row['satuan']} (stok {$row['stok']} → {$baru})");

        return $this->response->setJSON(['status' => 'berhasil', 'message' => "Stok {$row['nama_barang']} bertambah {$jumlah}, sekarang {$baru} {$row['satuan']}."]);
    }

    public function edit($id)
    {
        $row = $this->model->find($id);

        if (! $row) {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Data tidak ditemukan.']);
        }

        return $this->response->setJSON(['status' => 'berhasil', 'data' => $row]);
    }

    public function hapus($id)
    {
        $row = $this->model->find($id);
        if (! $row) {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Data tidak ditemukan.']);
        }

        $db      = $this->model->db;
        $dalamPaket = $db->table('layanan_barang')->where('barang_id', $id)->countAllResults();
        $dalamRiwayat = $db->table('cucian_barang')->where('barang_id', $id)->countAllResults();

        if ($dalamPaket > 0) {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Barang ini masih dipakai pada paket layanan. Lepas dulu dari paket layanannya.']);
        }
        if ($dalamRiwayat > 0) {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Barang ini tercatat pada riwayat cucian, tidak bisa dihapus. Atur stoknya menjadi 0 saja.']);
        }

        $this->model->delete($id);
        log_aktivitas('hapus', 'barang', 'Menghapus barang ' . $row['nama_barang']);

        return $this->response->setJSON(['status' => 'berhasil', 'message' => 'Barang berhasil dihapus.']);
    }
}
