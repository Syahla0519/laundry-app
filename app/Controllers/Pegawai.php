<?php

namespace App\Controllers;

use App\Models\PegawaiModel;
use CodeIgniter\Controller;

class Pegawai extends Controller
{
    protected PegawaiModel $model;

    public function __construct()
    {
        $this->model = new PegawaiModel();
    }

    public function index()
    {
        return view('pegawai/index', ['title' => 'Data Pegawai']);
    }

    /**
     * Dipanggil AJAX untuk isi tabel (password TIDAK dikirim ke browser).
     * Halaman ini KHUSUS kelola akun role "pegawai" — akun pemilik
     * sengaja tidak ditampilkan/dikelola di sini (cuma ada 1 pemilik).
     */
    public function data()
    {
        return $this->response->setJSON([
            'data' => $this->model->select('id, nama, username, role, created_at')
                ->where('role', 'pegawai')
                ->orderBy('id', 'ASC')
                ->findAll(),
        ]);
    }

    /**
     * Simpan akun baru ATAU update (dibedakan lewat field id).
     * Role dipaksa "pegawai" dari server, berapa pun yang dikirim dari
     * form — halaman ini tidak pernah bisa dipakai membuat/mengubah
     * akun jadi pemilik.
     */
    public function simpan()
    {
        $id       = $this->request->getPost('id');
        $nama     = $this->request->getPost('nama');
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $data = ['nama' => $nama, 'username' => $username, 'role' => 'pegawai'];

        if (! $this->model->validate($data)) {
            return $this->response->setJSON([
                'status'  => 'gagal',
                'message' => 'Data tidak valid.',
                'errors'  => $this->model->errors(),
            ]);
        }

        if ($this->model->usernameDipakai($username, $id ?: null)) {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Username sudah dipakai, pilih username lain.']);
        }

        if ($id) {
            // Jaga-jaga: pastikan yang diedit memang akun pegawai, bukan akun pemilik
            $existing = $this->model->find($id);
            if (! $existing || $existing['role'] !== 'pegawai') {
                return $this->response->setJSON(['status' => 'gagal', 'message' => 'Data tidak ditemukan.']);
            }

            // Password hanya diganti kalau field "gantiPassword" dicentang & diisi
            // (mencegah browser autofill diam-diam mengganti password saat edit)
            $gantiPassword = $this->request->getPost('ganti_password');
            if ($gantiPassword && ! empty($password)) {
                $data['password'] = password_hash($password, PASSWORD_DEFAULT);
            }
            $this->model->update($id, $data);
            log_aktivitas('ubah', 'pegawai', "Mengubah akun pegawai {$nama} ({$username})" . (($gantiPassword && ! empty($password)) ? ', password diganti' : ''));
            $pesan = 'Data pegawai berhasil diperbarui.';
        } else {
            if (empty($password)) {
                return $this->response->setJSON(['status' => 'gagal', 'message' => 'Password wajib diisi untuk akun baru.']);
            }
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
            $this->model->insert($data);
            log_aktivitas('tambah', 'pegawai', "Menambah akun pegawai {$nama} ({$username})");
            $pesan = 'Akun pegawai baru berhasil ditambahkan.';
        }

        return $this->response->setJSON(['status' => 'berhasil', 'message' => $pesan]);
    }

    public function edit($id)
    {
        $row = $this->model->where('role', 'pegawai')->find($id);

        if (! $row) {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Data tidak ditemukan.']);
        }

        unset($row['password']); // jangan kirim hash password ke browser

        return $this->response->setJSON(['status' => 'berhasil', 'data' => $row]);
    }

    public function hapus($id)
    {
        if ((int) $id === (int) session()->get('user_id')) {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Kamu tidak bisa menghapus akunmu sendiri.']);
        }

        $row = $this->model->where('role', 'pegawai')->find($id);
        if (! $row) {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Data tidak ditemukan.']);
        }

        // Jangan sampai hapus akun yang masih tercatat di riwayat cucian
        $masihDipakai = $this->model->db->table('cucian')
            ->groupStart()
                ->where('dicatat_oleh', $id)
                ->orWhere('diselesaikan_oleh', $id)
            ->groupEnd()
            ->countAllResults();

        if ($masihDipakai > 0) {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Akun ini masih tercatat pada riwayat transaksi cucian, tidak bisa dihapus.']);
        }

        $this->model->delete($id);
        log_aktivitas('hapus', 'pegawai', "Menghapus akun pegawai {$row['nama']} ({$row['username']})");

        return $this->response->setJSON(['status' => 'berhasil', 'message' => 'Akun pegawai berhasil dihapus.']);
    }
}
