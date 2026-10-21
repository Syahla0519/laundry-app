<?php

namespace App\Controllers;

use App\Models\PelangganModel;
use CodeIgniter\Controller;

class Pelanggan extends Controller
{
    protected PelangganModel $model;

    public function __construct()
    {
        $this->model = new PelangganModel();
    }

    public function index()
    {
        return view('pelanggan/index', ['title' => 'Data Pelanggan']);
    }

    /** Dipanggil AJAX (DataTables/manual) untuk isi tabel */
    public function data()
    {
        return $this->response->setJSON([
            'data' => $this->model->select('pelanggan.*, u.username as akun_username')
                ->join('users u', 'u.pelanggan_id = pelanggan.id', 'left')
                ->orderBy('pelanggan.id', 'DESC')
                ->findAll(),
        ]);
    }

    /** Simpan data baru ATAU update (dibedakan lewat field id) */
    public function simpan()
    {
        $id   = $this->request->getPost('id');
        $data = [
            'nama'    => $this->request->getPost('nama'),
            'telepon' => $this->request->getPost('telepon'),
            'alamat'  => $this->request->getPost('alamat'),
        ];

        if (! $this->model->validate($data)) {
            return $this->response->setJSON([
                'status'  => 'gagal',
                'message' => 'Data tidak valid.',
                'errors'  => $this->model->errors(),
            ]);
        }

        if ($id) {
            $this->model->update($id, $data);
            log_aktivitas('ubah', 'pelanggan', "Mengubah data pelanggan {$data['nama']} ({$data['telepon']})");
            $pesan = 'Data pelanggan berhasil diperbarui.';
        } else {
            $this->model->insert($data);
            log_aktivitas('tambah', 'pelanggan', "Menambah pelanggan {$data['nama']} ({$data['telepon']})");
            $pesan = 'Data pelanggan berhasil ditambahkan.';
        }

        return $this->response->setJSON(['status' => 'berhasil', 'message' => $pesan]);
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

        // Pelanggan yang punya transaksi tidak dihapus: stok barang & riwayat cucian ikut terdampak
        $punyaCucian = $this->model->db->table('cucian')->where('pelanggan_id', $id)->countAllResults();
        if ($punyaCucian > 0) {
            return $this->response->setJSON(['status' => 'gagal', 'message' => "Pelanggan ini punya {$punyaCucian} transaksi cucian, tidak bisa dihapus."]);
        }

        // akun login milik pelanggan ini (role user) ikut dihapus
        $this->model->db->table('users')->where('pelanggan_id', $id)->where('role', 'user')->delete();
        $this->model->delete($id);
        log_aktivitas('hapus', 'pelanggan', "Menghapus pelanggan {$row['nama']} ({$row['telepon']})");

        return $this->response->setJSON(['status' => 'berhasil', 'message' => 'Data pelanggan berhasil dihapus.']);
    }

    /**
     * Buat akun login (role "user") untuk pelanggan supaya dia bisa melihat status cuciannya.
     * Kalau akunnya sudah ada, password-nya direset. Password acak hanya ditampilkan sekali.
     */
    public function buatAkun($id)
    {
        $pelanggan = $this->model->find($id);
        if (! $pelanggan) {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Data tidak ditemukan.']);
        }

        $users    = new \App\Models\UserModel();
        $akun     = $users->where('pelanggan_id', $id)->first();
        $password = $this->passwordAcak();

        if ($akun) {
            $users->update($akun['id'], ['password' => password_hash($password, PASSWORD_DEFAULT)]);
            log_aktivitas('reset_password', 'pelanggan', "Mereset password akun pelanggan {$pelanggan['nama']} (username {$akun['username']})");

            return $this->response->setJSON([
                'status' => 'berhasil', 'message' => 'Password akun berhasil direset.',
                'username' => $akun['username'], 'password' => $password,
            ]);
        }

        // username dari nomor telepon; kalau bentrok tambahkan id pelanggan
        $username = preg_replace('/\D+/', '', (string) $pelanggan['telepon']) ?: ('pelanggan' . $id);
        if ($users->where('username', $username)->first()) {
            $username .= '_' . $id;
        }

        $users->insert([
            'nama'         => $pelanggan['nama'],
            'username'     => $username,
            'password'     => password_hash($password, PASSWORD_DEFAULT),
            'role'         => 'user',
            'pelanggan_id' => $pelanggan['id'],
        ]);
        log_aktivitas('buat_akun', 'pelanggan', "Membuat akun login untuk pelanggan {$pelanggan['nama']} (username {$username})");

        return $this->response->setJSON([
            'status' => 'berhasil', 'message' => 'Akun pelanggan berhasil dibuat.',
            'username' => $username, 'password' => $password,
        ]);
    }

    private function passwordAcak(int $panjang = 6): string
    {
        $huruf = 'abcdefghjkmnpqrstuvwxyz23456789'; // tanpa karakter yang mirip (i, l, o, 0, 1)
        $hasil = '';
        for ($i = 0; $i < $panjang; $i++) {
            $hasil .= $huruf[random_int(0, strlen($huruf) - 1)];
        }

        return $hasil;
    }
}
