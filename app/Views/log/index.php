<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php
$aksiWarna = [
    'login' => 'secondary', 'logout' => 'secondary', 'login_gagal' => 'danger',
    'tambah' => 'success', 'ubah' => 'primary', 'hapus' => 'danger',
    'status' => 'warning', 'buka_kunci' => 'dark', 'restok' => 'info',
    'buat_akun' => 'success', 'reset_password' => 'warning', 'ubah_akun' => 'primary',
];
$qs = fn (array $extra = []) => http_build_query(array_filter(array_merge($filter, $extra), fn ($v) => $v !== '' && $v !== null));
?>

<style>
    .lg-filter .form-control, .lg-filter .form-select { font-size: 13.5px; }
    .lg-aksi { font-weight: 600; font-size: 11.5px; letter-spacing: .02em; }
    .lg-ket  { max-width: 520px; font-size: 13.5px; }
    .lg-waktu { white-space: nowrap; font-size: 13px; }
    .lg-waktu small { display: block; color: #8a949e; font-size: 11.5px; }
</style>

<div class="card card-soft p-3 mb-3">
    <form method="get" action="<?= site_url('log-aktivitas') ?>" class="row g-2 align-items-end lg-filter">
        <div class="col-lg-3 col-md-6">
            <label class="form-label small fw-semibold mb-1">Cari</label>
            <input type="text" class="form-control" name="q" value="<?= esc($filter['q']) ?>" placeholder="Nama pelaku atau keterangan">
        </div>
        <div class="col-lg-2 col-md-3 col-6">
            <label class="form-label small fw-semibold mb-1">Modul</label>
            <select class="form-select" name="modul">
                <option value="">Semua</option>
                <?php foreach ($daftarModul as $m): ?>
                <option value="<?= $m ?>" <?= $filter['modul'] === $m ? 'selected' : '' ?>><?= ucfirst($m) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-lg-2 col-md-3 col-6">
            <label class="form-label small fw-semibold mb-1">Aksi</label>
            <select class="form-select" name="aksi">
                <option value="">Semua</option>
                <?php foreach ($daftarAksi as $a): ?>
                <option value="<?= $a ?>" <?= $filter['aksi'] === $a ? 'selected' : '' ?>><?= esc(str_replace('_', ' ', $a)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-lg-2 col-md-3 col-6">
            <label class="form-label small fw-semibold mb-1">Dari</label>
            <input type="date" class="form-control" name="dari" value="<?= esc($filter['dari']) ?>">
        </div>
        <div class="col-lg-2 col-md-3 col-6">
            <label class="form-label small fw-semibold mb-1">Sampai</label>
            <input type="date" class="form-control" name="sampai" value="<?= esc($filter['sampai']) ?>">
        </div>
        <div class="col-lg-1 col-12 d-flex gap-2">
            <button class="btn btn-primary btn-sm flex-grow-1" type="submit"><i class="bi bi-funnel"></i></button>
            <a class="btn btn-outline-secondary btn-sm" href="<?= site_url('log-aktivitas') ?>" title="Reset"><i class="bi bi-x-lg"></i></a>
        </div>
    </form>
</div>

<div class="card card-soft p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">Riwayat Aktivitas</h6>
        <span class="badge badge-role"><?= number_format($total, 0, ',', '.') ?> catatan</span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="text-muted small">
                <tr><th>Waktu</th><th>Pelaku</th><th>Aksi</th><th>Modul</th><th>Keterangan</th><th>IP</th></tr>
            </thead>
            <tbody>
                <?php foreach ($baris as $b): ?>
                <tr>
                    <td class="lg-waktu"><?= esc(date('d M Y', strtotime($b['created_at']))) ?><small><?= esc(date('H:i:s', strtotime($b['created_at']))) ?></small></td>
                    <td>
                        <span class="fw-medium"><?= esc($b['nama_user']) ?></span>
                        <?php if ($b['role']): ?><small class="text-muted d-block"><?= esc($b['role']) ?></small><?php endif; ?>
                    </td>
                    <td><span class="badge lg-aksi text-bg-<?= $aksiWarna[$b['aksi']] ?? 'secondary' ?>"><?= esc(strtoupper(str_replace('_', ' ', $b['aksi']))) ?></span></td>
                    <td class="text-capitalize"><?= esc($b['modul']) ?></td>
                    <td class="lg-ket"><?= esc($b['keterangan']) ?></td>
                    <td class="text-muted small"><?= esc($b['ip_address']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($baris)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada catatan yang cocok dengan filter.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($jumlahHal > 1): ?>
    <nav class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="small text-muted">Halaman <?= $halaman ?> dari <?= $jumlahHal ?></div>
        <ul class="pagination pagination-sm mb-0">
            <li class="page-item <?= $halaman <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="?<?= $qs(['page' => $halaman - 1]) ?>">&laquo;</a></li>
            <?php
            $mulai = max(1, $halaman - 2);
            $akhir = min($jumlahHal, $halaman + 2);
            for ($i = $mulai; $i <= $akhir; $i++): ?>
            <li class="page-item <?= $i === $halaman ? 'active' : '' ?>"><a class="page-link" href="?<?= $qs(['page' => $i]) ?>"><?= $i ?></a></li>
            <?php endfor; ?>
            <li class="page-item <?= $halaman >= $jumlahHal ? 'disabled' : '' ?>"><a class="page-link" href="?<?= $qs(['page' => $halaman + 1]) ?>">&raquo;</a></li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
