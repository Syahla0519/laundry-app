<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Laundry aja') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tema DeskApp (sidebar gelap, header putih, card-box) - edit tampilan di file ini -->
    <link href="<?= base_url('assets/css/deskapp.css') ?>" rel="stylesheet">
</head>

<?php
$role      = session()->get('role');
$isPemilik = $role === 'pemilik';
$isUser    = $role === 'user'; // pelanggan: hanya melihat status cuciannya
$uri       = uri_string();

if ($isUser) {
    $menu = [
        ['url' => 'dashboard', 'icon' => 'bi-clipboard2-pulse', 'text' => 'Status Cucian Saya', 'match' => 'exact'],
    ];
} else {
    $menu = [
        ['url' => 'dashboard', 'icon' => 'bi-house', 'text' => 'Dashboard', 'match' => 'exact'],
        ['url' => 'pelanggan', 'icon' => 'bi-people', 'text' => 'Data Pelanggan'],
        ['url' => 'cucian',    'icon' => 'bi-clipboard2-check', 'text' => 'Transaksi Cucian'],
        ['url' => 'layanan',   'icon' => 'bi-basket', 'text' => 'Data Layanan', 'sub' => $isPemilik ? '' : '(lihat saja)'],
        ['url' => 'barang',    'icon' => 'bi-box-seam', 'text' => 'Stok Barang', 'sub' => $isPemilik ? '' : '(lihat saja)'],
    ];
}
$menuPemilik = [
    ['url' => 'laporan',       'icon' => 'bi-graph-up-arrow', 'text' => 'Laporan & Rekap'],
    ['url' => 'pegawai',       'icon' => 'bi-person-badge', 'text' => 'Data Pegawai'],
    ['url' => 'log-aktivitas', 'icon' => 'bi-journal-text', 'text' => 'Log Aktivitas'],
];
$renderMenu = function (array $items) use ($uri) {
    foreach ($items as $m) {
        $aktif = ($m['match'] ?? '') === 'exact' ? $uri === $m['url'] : str_contains($uri, $m['url']);
        echo '<li><a href="' . site_url($m['url']) . '" class="menu-link ' . ($aktif ? 'active' : '') . '">'
            . '<span class="micon bi ' . $m['icon'] . '"></span>'
            . '<span class="mtext">' . esc($m['text'])
            . (! empty($m['sub']) ? '<small>' . esc($m['sub']) . '</small>' : '')
            . '</span></a></li>';
    }
};
?>

<body class="role-<?= esc(in_array($role, ['pemilik', 'user'], true) ? $role : 'pegawai') ?>">

    <!-- ============ HEADER ============ -->
    <div class="da-header">
        <div class="header-left">
            <div class="menu-icon bi bi-list" id="btnToggleSidebar"></div>
            <div class="header-search d-none d-sm-block">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="headerSearch" placeholder="Cari di tabel halaman ini..." autocomplete="off">
            </div>
        </div>

        <div class="header-right">
            <div class="user-notification dropdown">
                <button class="bell-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notifikasi">
                    <i class="bi bi-bell"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end p-3 text-center text-muted small" style="min-width:230px;">
                    <i class="bi bi-bell-slash fs-4 d-block mb-1"></i>
                    Tidak ada notifikasi baru
                </div>
            </div>

            <div class="user-info-dropdown dropdown">
                <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="user-icon"><?= esc(strtoupper(substr(session()->get('nama') ?? 'U', 0, 1))) ?></span>
                    <span class="user-name">
                        <?= esc(session()->get('nama')) ?>
                        <small><?= esc(session()->get('role')) ?></small>
                    </span>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <?php if (session()->get('role') !== 'pegawai'): ?>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalAkun"><i class="bi bi-person-gear me-2"></i> Ganti Username &amp; Password</a>
                    <div class="dropdown-divider"></div>
                    <?php endif; ?>
                    <a class="dropdown-item" href="<?= site_url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i> Log Out</a>
                </div>
            </div>
        </div>
    </div>

    <!-- ============ SIDEBAR ============ -->
    <div class="left-side-bar" id="sidebar">
        <div class="brand-logo">
            <a href="<?= site_url('dashboard') ?>">
                <span class="brand-mark"><i class="bi bi-droplet-half"></i></span> Laundry aja
            </a>
            <div class="close-sidebar bi bi-x-lg" id="btnCloseSidebar"></div>
        </div>
        <div class="menu-block">
            <div class="sidebar-menu">
                <ul>
                    <?php $renderMenu($menu); ?>
                    <?php if ($isPemilik): ?>
                        <li class="menu-label">Khusus Pemilik</li>
                        <?php $renderMenu($menuPemilik); ?>
                    <?php endif; ?>
                    <li class="menu-label">Akun</li>
                    <li>
                        <a href="<?= site_url('logout') ?>" class="menu-link">
                            <span class="micon bi bi-box-arrow-right"></span><span class="mtext">Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="mobile-menu-overlay" id="sidebarOverlay"></div>

    <!-- ============ KONTEN ============ -->
    <div class="main-container">
        <div class="pd-ltr-20">
            <div class="page-header">
                <div class="title">
                    <h4><?= esc($title ?? 'Dashboard') ?></h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?= esc($title ?? 'Dashboard') ?></li>
                        </ol>
                    </nav>
                </div>
            </div>

            <?= $this->renderSection('content') ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if (session()->get('role') !== 'pegawai'): ?>
    <!-- Modal ganti username / password akun sendiri (tidak untuk pegawai: dikelola pemilik) -->
    <div class="modal fade" id="modalAkun" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Ganti Username &amp; Password</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div id="akunAlert" class="alert alert-danger py-2 px-3 small d-none" role="alert"></div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="akunPassLama">1. Password saat ini <span class="text-danger">*</span></label>
                        <input type="password" id="akunPassLama" class="form-control" placeholder="Wajib diisi untuk konfirmasi" autocomplete="current-password">
                    </div>
                    <hr class="my-3">
                    <p class="text-muted small mb-3">2. Isi yang mau diganti saja (username, password, atau keduanya).</p>
                    <div class="mb-3">
                        <label class="form-label" for="akunUsername">Username</label>
                        <input type="text" id="akunUsername" class="form-control" value="<?= esc(session()->get('username') ?? '') ?>" autocomplete="username" maxlength="30">
                        <div class="form-text">4-30 karakter, tanpa spasi: huruf, angka, titik, strip, atau garis bawah.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="akunPassBaru">Password baru</label>
                        <input type="password" id="akunPassBaru" class="form-control" placeholder="Minimal 6 karakter (kosongkan jika tidak diganti)" autocomplete="new-password">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="akunPassKonf">Ulangi password baru</label>
                        <input type="password" id="akunPassKonf" class="form-control" autocomplete="new-password">
                    </div>
                    <div class="form-check mt-2">
                        <input type="checkbox" id="akunLihat" class="form-check-input">
                        <label class="form-check-label small" for="akunLihat">Tampilkan password</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="akunSimpan" onclick="simpanAkun()">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        // Sidebar mobile (buka / tutup)
        const sidebarEl = document.getElementById('sidebar');
        const overlayEl = document.getElementById('sidebarOverlay');
        function toggleSidebar(show) {
            sidebarEl.classList.toggle('show', show);
            overlayEl.classList.toggle('show', show);
        }
        document.getElementById('btnToggleSidebar')?.addEventListener('click', () => toggleSidebar(true));
        document.getElementById('btnCloseSidebar')?.addEventListener('click', () => toggleSidebar(false));
        overlayEl.addEventListener('click', () => toggleSidebar(false));

        // Tabel responsive (tampilan kartu di HP): isi data-label tiap sel dari <thead>.
        // Dipanggil ulang otomatis saat baris tabel dirender lewat JS (fetch).
        function labelTabel(table) {
            const heads = [...table.querySelectorAll('thead th')].map(th => ({
                text: th.innerText.trim() === '#' ? 'No' : th.innerText.trim(),
                aksi: th.classList.contains('text-end') && /aksi/i.test(th.innerText)
            }));
            table.querySelectorAll('tbody tr, tfoot tr').forEach(tr => {
                let col = 0;
                [...tr.children].forEach(td => {
                    const span = td.colSpan || 1;
                    if (span > 1) {
                        td.classList.add('td-full');
                    } else if (heads[col]) {
                        td.dataset.label = heads[col].text;
                        if (heads[col].aksi) {
                            td.classList.add('td-aksi');
                            td.removeAttribute('data-label');
                        }
                    }
                    if (!td.querySelector(':scope > .cell-val') && !td.classList.contains('td-full')) {
                        const wrap = document.createElement('span');
                        wrap.className = 'cell-val';
                        while (td.firstChild) wrap.appendChild(td.firstChild);
                        td.appendChild(wrap);
                    }
                    col += span;
                });
            });
        }
        document.querySelectorAll('.main-container table.table').forEach(table => {
            labelTabel(table);
            table.querySelectorAll('tbody').forEach(tb =>
                new MutationObserver(() => labelTabel(table)).observe(tb, { childList: true })
            );
        });

        // Pencarian header: menyaring baris tabel yang tampil di halaman ini
        document.getElementById('headerSearch')?.addEventListener('input', (e) => {
            const q = e.target.value.toLowerCase().trim();
            document.querySelectorAll('.main-container table tbody tr').forEach((tr) => {
                tr.style.display = !q || tr.innerText.toLowerCase().includes(q) ? '' : 'none';
            });
        });

        <?php if (session()->getFlashdata('success')): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '<?= esc(session()->getFlashdata('success'), 'js') ?>',
                timer: 2500,
                showConfirmButton: false
            });
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: '<?= esc(session()->getFlashdata('error'), 'js') ?>'
            });
        <?php endif; ?>

        /** Helper global dipakai semua halaman CRUD modal */
        function tampilPesan(status, pesan) {
            Swal.fire({
                icon: status === 'berhasil' ? 'success' : 'error',
                title: status === 'berhasil' ? 'Berhasil' : 'Gagal',
                text: pesan,
                timer: status === 'berhasil' ? 2000 : undefined,
                showConfirmButton: status !== 'berhasil',
            });
        }

        <?php if (session()->get('role') !== 'pegawai'): ?>
        /** Ganti username / password akun sendiri (menu profil di navbar) */
        function tampilErrorAkun(pesan) {
            const el = document.getElementById('akunAlert');
            el.textContent = pesan;
            el.classList.remove('d-none');
        }

        function simpanAkun() {
            const val = (id) => document.getElementById(id).value;
            document.getElementById('akunAlert').classList.add('d-none');

            const fd = new FormData();
            fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            fd.append('username', val('akunUsername'));
            fd.append('password_baru', val('akunPassBaru'));
            fd.append('password_konfirmasi', val('akunPassKonf'));
            fd.append('password_lama', val('akunPassLama'));

            const btn = document.getElementById('akunSimpan');
            btn.disabled = true;
            fetch('<?= site_url('akun/ubah') ?>', { method: 'POST', body: fd })
                .then(async (r) => {
                    try { return await r.json(); } catch (e) { throw new Error('HTTP ' + r.status); }
                })
                .then((res) => {
                    btn.disabled = false;
                    if (res.status !== 'berhasil') return tampilErrorAkun(res.message);
                    bootstrap.Modal.getInstance(document.getElementById('modalAkun'))?.hide();
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1800, showConfirmButton: false })
                        .then(() => location.reload());
                })
                .catch((err) => {
                    btn.disabled = false;
                    tampilErrorAkun('Server tidak membalas dengan benar (' + err.message + '). Coba logout lalu login lagi. Kalau masih begini, pastikan file Akun.php dan Routes.php sudah terpasang.');
                });
        }
        document.getElementById('akunLihat')?.addEventListener('change', (e) => {
            ['akunPassLama', 'akunPassBaru', 'akunPassKonf'].forEach(id => {
                document.getElementById(id).type = e.target.checked ? 'text' : 'password';
            });
        });
        document.getElementById('modalAkun')?.addEventListener('hidden.bs.modal', () => {
            ['akunPassLama', 'akunPassBaru', 'akunPassKonf'].forEach(id => { const el = document.getElementById(id); el.value = ''; el.type = 'password'; });
            document.getElementById('akunLihat').checked = false;
            document.getElementById('akunAlert').classList.add('d-none');
        });
        <?php endif; ?>

        function konfirmasiHapus(url, callback) {
            Swal.fire({
                title: 'Yakin hapus data ini?',
                text: 'Data yang dihapus tidak bisa dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33',
            }).then((result) => {
                if (result.isConfirmed) callback();
            });
        }
    </script>
    <?= $this->renderSection('script') ?>
</body>

</html>
