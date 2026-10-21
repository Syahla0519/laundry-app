<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 *
 * Role: pemilik, pegawai, user (pelanggan).
 * "user" hanya boleh membuka /dashboard (status cuciannya sendiri).
 */

// ==== AUTH (tidak butuh login) ====
$routes->get('/', 'Auth::login');
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('logout', 'Auth::logout');

// ==== HALAMAN YANG BUTUH LOGIN ====
$routes->group('', ['filter' => 'auth'], function ($routes) {

    // Semua role (isi dashboard menyesuaikan role)
    $routes->get('dashboard', 'Dashboard::index');

    // Ganti username / password akun sendiri (semua role)
    $routes->post('akun/ubah', 'Akun::ubah');

    // ---- Halaman operasional: hanya pemilik & pegawai (role "user" ditolak) ----
    $routes->group('', ['filter' => 'role:pemilik,pegawai'], function ($routes) {

        // Master Data: Pelanggan (lihat: pemilik & pegawai, ubah: khusus pegawai)
        $routes->get('pelanggan', 'Pelanggan::index');
        $routes->get('pelanggan/data', 'Pelanggan::data');

        // Master Data: Layanan (lihat: pemilik & pegawai, ubah: khusus pemilik)
        $routes->get('layanan', 'Layanan::index');
        $routes->get('layanan/data', 'Layanan::data');

        // Stok Barang (lihat: pemilik & pegawai, ubah: khusus pemilik)
        $routes->get('barang', 'Barang::index');
        $routes->get('barang/data', 'Barang::data');

        // Transaksi: Cucian (lihat: pemilik & pegawai, kelola: khusus pegawai)
        $routes->get('cucian', 'Cucian::index');
        $routes->get('cucian/data', 'Cucian::data');
    });

    // ---- Khusus Pegawai ----
    $routes->group('', ['filter' => 'role:pegawai'], function ($routes) {
        $routes->post('pelanggan/simpan', 'Pelanggan::simpan');
        $routes->get('pelanggan/edit/(:num)', 'Pelanggan::edit/$1');
        $routes->post('pelanggan/hapus/(:num)', 'Pelanggan::hapus/$1');
        $routes->post('pelanggan/buat-akun/(:num)', 'Pelanggan::buatAkun/$1');

        $routes->post('cucian/simpan', 'Cucian::simpan');
        $routes->get('cucian/edit/(:num)', 'Cucian::edit/$1');
        $routes->post('cucian/hapus/(:num)', 'Cucian::hapus/$1');
        $routes->post('cucian/status/(:num)', 'Cucian::updateStatus/$1');
    });

    // ---- Khusus Pemilik ----
    $routes->group('', ['filter' => 'role:pemilik'], function ($routes) {
        // Layanan + paket barang
        $routes->post('layanan/simpan', 'Layanan::simpan');
        $routes->get('layanan/edit/(:num)', 'Layanan::edit/$1');
        $routes->post('layanan/hapus/(:num)', 'Layanan::hapus/$1');

        // Stok barang
        $routes->post('barang/simpan', 'Barang::simpan');
        $routes->get('barang/edit/(:num)', 'Barang::edit/$1');
        $routes->post('barang/tambah-stok/(:num)', 'Barang::tambahStok/$1');
        $routes->post('barang/hapus/(:num)', 'Barang::hapus/$1');

        // Buka kunci cucian selesai
        $routes->post('cucian/buka-kunci/(:num)', 'Cucian::bukaKunci/$1');

        // Log aktivitas
        $routes->get('log-aktivitas', 'Log::index');
    });

    // ---- Khusus Pemilik: Laporan & Rekap ----
    $routes->group('laporan', ['filter' => 'role:pemilik'], function ($routes) {
        $routes->get('/', 'Laporan::index');
        $routes->get('export-pdf', 'Laporan::exportPdf');
    });

    // ---- Khusus Pemilik: CRUD Data Pegawai ----
    $routes->group('pegawai', ['filter' => 'role:pemilik'], function ($routes) {
        $routes->get('/', 'Pegawai::index');
        $routes->get('data', 'Pegawai::data');
        $routes->post('simpan', 'Pegawai::simpan');
        $routes->get('edit/(:num)', 'Pegawai::edit/$1');
        $routes->post('hapus/(:num)', 'Pegawai::hapus/$1');
    });
});
