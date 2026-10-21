<?php

namespace App\Controllers;

use App\Models\UserModel;

/**
 * Ganti username / password akun sendiri (semua role: pemilik, pegawai, user).
 * Selalu bekerja pada akun yang sedang login (user_id dari session), tidak menerima id dari form.
 */
class Akun extends BaseController
{
    public function ubah()
    {
        $id    = (int) session()->get('user_id');
        $users = new UserModel();
        $user  = $users->find($id);

        if (! $user) {
            return $this->gagal('Sesi tidak valid, silakan login ulang.');
        }

        // Akun pegawai dikelola pemilik (menu Data Pegawai); pegawai tidak boleh mengubah kredensialnya sendiri.
        // Dicek dari database, bukan dari session/form, supaya tidak bisa dilewati.
        if ($user['role'] === 'pegawai') {
            return $this->gagal('Akun pegawai dikelola oleh pemilik. Hubungi pemilik untuk mengganti username atau password.');
        }

        $usernameBaru = trim((string) $this->request->getPost('username'));
        $passLama     = (string) $this->request->getPost('password_lama');
        $passBaru     = (string) $this->request->getPost('password_baru');
        $passKonf     = (string) $this->request->getPost('password_konfirmasi');

        // identitas harus dikonfirmasi dengan password saat ini
        if ($passLama === '' || ! password_verify($passLama, $user['password'])) {
            return $this->gagal('Password saat ini salah.');
        }

        $ubahUsername = $usernameBaru !== '' && $usernameBaru !== $user['username'];
        $ubahPassword = $passBaru !== '';

        if (! $ubahUsername && ! $ubahPassword) {
            return $this->gagal('Tidak ada yang diubah. Isi username baru atau password baru.');
        }

        $data = [];

        if ($ubahUsername) {
            if (preg_match('/\s/', $usernameBaru)) {
                return $this->gagal('Username tidak boleh pakai spasi. Gunakan titik atau garis bawah, contoh: safika.laundry');
            }
            if (! preg_match('/^[A-Za-z0-9._-]{4,30}$/', $usernameBaru)) {
                return $this->gagal('Username 4-30 karakter, hanya huruf, angka, titik, strip, dan garis bawah.');
            }
            $dipakai = $users->where('username', $usernameBaru)->where('id !=', $id)->first();
            if ($dipakai) {
                return $this->gagal('Username sudah dipakai orang lain, coba yang lain.');
            }
            $data['username'] = $usernameBaru;
        }

        if ($ubahPassword) {
            if (mb_strlen($passBaru) < 6) {
                return $this->gagal('Password baru minimal 6 karakter.');
            }
            if ($passBaru !== $passKonf) {
                return $this->gagal('Konfirmasi password baru tidak sama.');
            }
            if (password_verify($passBaru, $user['password'])) {
                return $this->gagal('Password baru tidak boleh sama dengan password saat ini.');
            }
            $data['password'] = password_hash($passBaru, PASSWORD_DEFAULT);
        }

        $users->update($id, $data);

        if ($ubahUsername) {
            session()->set('username', $usernameBaru);
        }

        $yang = [];
        if ($ubahUsername) { $yang[] = "username (menjadi {$usernameBaru})"; }
        if ($ubahPassword) { $yang[] = 'password'; }
        log_aktivitas('ubah_akun', 'akun', 'Mengubah ' . implode(' dan ', $yang) . ' akun sendiri');

        return $this->response->setJSON([
            'status'   => 'berhasil',
            'message'  => 'Akun berhasil diperbarui.' . ($ubahPassword ? ' Gunakan password baru saat login berikutnya.' : ''),
            'username' => $usernameBaru !== '' ? $usernameBaru : $user['username'],
        ]);
    }

    private function gagal(string $pesan)
    {
        return $this->response->setJSON(['status' => 'gagal', 'message' => $pesan]);
    }
}
