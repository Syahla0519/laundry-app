<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (! function_exists('log_aktivitas')) {
    /**
     * Catat aktivitas ke tabel log_aktivitas.
     *
     * @param string     $aksi       login, logout, login_gagal, tambah, ubah, hapus, status, buka_kunci, restok, buat_akun, reset_password
     * @param string     $modul      auth, cucian, pelanggan, layanan, barang, pegawai
     * @param string     $keterangan penjelasan singkat (maks 500 karakter)
     * @param array|null $pelaku     isi manual ['id','nama','role'] bila belum ada session (mis. saat login)
     */
    function log_aktivitas(string $aksi, string $modul, string $keterangan = '', ?array $pelaku = null): void
    {
        try {
            $session = session();

            (new \App\Models\LogModel())->insert([
                'user_id'    => $pelaku['id'] ?? $session->get('user_id'),
                'nama_user'  => $pelaku['nama'] ?? $session->get('nama') ?? 'Tamu',
                'role'       => $pelaku['role'] ?? $session->get('role'),
                'aksi'       => $aksi,
                'modul'      => $modul,
                'keterangan' => mb_substr($keterangan, 0, 500),
                'ip_address' => service('request')->getIPAddress(),
            ]);
        } catch (\Throwable $e) {
            // log gagal tidak boleh menggagalkan proses utama
            log_message('error', 'log_aktivitas gagal: ' . $e->getMessage());
        }
    }
}
