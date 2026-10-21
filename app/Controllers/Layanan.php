<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\LayananModel;
use CodeIgniter\Controller;

class Layanan extends Controller
{
    protected LayananModel $model;
    protected BarangModel $barang;

    public function __construct()
    {
        $this->model  = new LayananModel();
        $this->barang = new BarangModel();
    }

    public function index()
    {
        return view('layanan/index', [
            'title'  => 'Data Layanan',
            'barang' => $this->barang->orderBy('nama_barang', 'ASC')->findAll(), // pilihan untuk paket barang
        ]);
    }

    public function data()
    {
        $layanan = $this->model->orderBy('id', 'DESC')->findAll();
        $paket   = $this->barang->paketSemuaLayanan();

        foreach ($layanan as &$l) {
            $l['paket'] = $paket[$l['id']] ?? [];
        }

        return $this->response->setJSON(['data' => $layanan]);
    }

    /** Baca & validasi paket barang dari form: barang_id[] + jumlah[] */
    private function bacaPaket(): array
    {
        $ids     = (array) $this->request->getPost('barang_id');
        $jumlahs = (array) $this->request->getPost('jumlah');
        $paket   = [];

        foreach ($ids as $i => $barangId) {
            $barangId = (int) $barangId;
            $jumlah   = (int) ($jumlahs[$i] ?? 0);

            if ($barangId === 0 && $jumlah === 0) {
                continue; // baris kosong diabaikan
            }
            if ($barangId === 0) {
                return ['error' => 'Pilih barang pada setiap baris paket, atau hapus baris yang kosong.'];
            }
            if ($jumlah < 1) {
                return ['error' => 'Jumlah barang pada paket minimal 1.'];
            }
            if (isset($paket[$barangId])) {
                return ['error' => 'Satu barang hanya boleh muncul sekali dalam paket.'];
            }
            if (! $this->barang->find($barangId)) {
                return ['error' => 'Ada barang pada paket yang tidak ditemukan.'];
            }
            $paket[$barangId] = $jumlah;
        }

        return ['paket' => $paket];
    }

    public function simpan()
    {
        $id   = $this->request->getPost('id');
        $data = [
            'nama_layanan' => $this->request->getPost('nama_layanan'),
            'harga_per_kg' => $this->request->getPost('harga_per_kg'),
        ];

        if (! $this->model->validate($data)) {
            return $this->response->setJSON([
                'status'  => 'gagal',
                'message' => 'Data tidak valid.',
                'errors'  => $this->model->errors(),
            ]);
        }

        $bacaan = $this->bacaPaket();
        if (isset($bacaan['error'])) {
            return $this->response->setJSON(['status' => 'gagal', 'message' => $bacaan['error']]);
        }
        $paket = $bacaan['paket'];

        $db = $this->model->db;
        $db->transBegin();

        if ($id) {
            $this->model->update($id, $data);
            $layananId = (int) $id;
        } else {
            $this->model->insert($data);
            $layananId = (int) $this->model->getInsertID();
        }

        // paket barang diganti seluruhnya sesuai isi form
        $db->table('layanan_barang')->where('layanan_id', $layananId)->delete();
        foreach ($paket as $barangId => $jumlah) {
            $db->table('layanan_barang')->insert(['layanan_id' => $layananId, 'barang_id' => $barangId, 'jumlah' => $jumlah]);
        }

        if ($db->transStatus() === false) {
            $db->transRollback();

            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal menyimpan layanan.']);
        }
        $db->transCommit();

        $teksPaket = $paket
            ? implode(', ', array_map(fn ($bid, $j) => $this->barang->find($bid)['nama_barang'] . ' ×' . $j, array_keys($paket), $paket))
            : 'tanpa paket barang';
        $ringkas = $data['nama_layanan'] . ' (Rp ' . number_format((float) $data['harga_per_kg'], 0, ',', '.') . '/kg), paket: ' . $teksPaket;

        if ($id) {
            log_aktivitas('ubah', 'layanan', 'Mengubah layanan ' . $ringkas);
            $pesan = 'Data layanan berhasil diperbarui.';
        } else {
            log_aktivitas('tambah', 'layanan', 'Menambah layanan ' . $ringkas);
            $pesan = 'Data layanan berhasil ditambahkan.';
        }

        return $this->response->setJSON(['status' => 'berhasil', 'message' => $pesan]);
    }

    public function edit($id)
    {
        $row = $this->model->find($id);

        if (! $row) {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Data tidak ditemukan.']);
        }

        return $this->response->setJSON([
            'status' => 'berhasil',
            'data'   => $row,
            'paket'  => $this->barang->paketLayanan((int) $id),
        ]);
    }

    public function hapus($id)
    {
        $row = $this->model->find($id);
        if (! $row) {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Data tidak ditemukan.']);
        }

        $dipakai = $this->model->db->table('cucian')->where('layanan_id', $id)->countAllResults();
        if ($dipakai > 0) {
            return $this->response->setJSON(['status' => 'gagal', 'message' => "Layanan ini sudah dipakai pada {$dipakai} transaksi cucian, tidak bisa dihapus."]);
        }

        $this->model->delete($id);
        log_aktivitas('hapus', 'layanan', 'Menghapus layanan ' . $row['nama_layanan']);

        return $this->response->setJSON(['status' => 'berhasil', 'message' => 'Data layanan berhasil dihapus.']);
    }
}
