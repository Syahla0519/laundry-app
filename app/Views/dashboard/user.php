<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php
$langkah = ['diterima' => 1, 'diproses' => 2, 'selesai' => 3];
$pesanStatus = [
    'diterima' => 'Cucianmu sudah kami terima dan menunggu giliran dikerjakan.',
    'diproses' => 'Cucianmu sedang dicuci dan diproses.',
    'selesai'  => 'Cucianmu sudah selesai dan siap diambil.',
];
$tgl = fn (?string $t) => $t ? date('d M Y', strtotime($t)) : '-';
$tglJam = fn (?string $t) => $t ? date('d M Y, H:i', strtotime($t)) : null;
?>

<style>
    /* ===== Dashboard User (status cucian) — class diawali us- ===== */
    .us-head h5 { margin: 0 0 2px; font-size: 20px; font-weight: 700; color: #031e23; }
    .us-head p { margin: 0 0 20px; font-size: 14px; color: #6c757d; }
    .us-card { background: #fff; border: 1px solid #e8edf2; border-radius: 14px; padding: 20px; }
    .us-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 18px; }
    .us-stat { display: flex; align-items: center; gap: 14px; padding: 18px; }
    .us-ico { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex: 0 0 44px; }
    .us-ico.brand { background: var(--da-primary-soft); color: var(--da-primary); }
    .us-ico.orange { background: #fff3e3; color: #f08a1c; }
    .us-ico.green { background: #e5f7ea; color: #1f9d4c; }
    .us-stat .lbl { font-size: 12.5px; color: #8a949e; }
    .us-stat .val { font-size: 24px; font-weight: 700; color: #031e23; line-height: 1.2; }
    .us-hint { display: flex; align-items: center; gap: 10px; padding: 12px 16px; margin: -6px 0 18px; background: var(--da-primary-soft); color: #031e23; border-radius: 12px; font-size: 13.5px; }
    .us-hint i { color: var(--da-primary); font-size: 16px; }
    .us-hint a { color: var(--da-primary); font-weight: 600; text-decoration: none; margin-left: auto; white-space: nowrap; }
    .us-title { font-size: 15px; font-weight: 600; color: #031e23; margin: 6px 0 12px; }

    /* kartu cucian berjalan + stepper 3 langkah */
    .us-order { margin-bottom: 14px; }
    .us-order-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap; margin-bottom: 18px; }
    .us-order-name { font-size: 15px; font-weight: 600; color: #031e23; }
    .us-order-meta { font-size: 13px; color: #6c757d; margin-top: 2px; }
    .us-order-total { text-align: right; }
    .us-order-total .amt { font-size: 16px; font-weight: 700; color: #031e23; }
    .us-order-total .id { font-size: 12px; color: #9aa4ae; }

    .us-steps { display: flex; align-items: flex-start; }
    .us-step { flex: 1; text-align: center; position: relative; min-width: 0; }
    .us-step::before { content: ''; position: absolute; top: 17px; left: -50%; width: 100%; height: 3px; background: #e6eaef; z-index: 0; }
    .us-step:first-child::before { display: none; }
    .us-step.done::before, .us-step.now::before { background: var(--da-primary); }
    .us-dot { position: relative; z-index: 1; width: 36px; height: 36px; border-radius: 50%; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; background: #fff; border: 3px solid #e6eaef; color: #b3bcc5; font-size: 15px; }
    .us-step.done .us-dot { background: var(--da-primary); border-color: var(--da-primary); color: #fff; }
    .us-step.now .us-dot { border-color: var(--da-primary); color: var(--da-primary); box-shadow: 0 0 0 5px rgba(var(--da-primary-rgb), .15); }
    .us-step .nm { font-size: 13px; font-weight: 600; color: #b3bcc5; }
    .us-step.done .nm, .us-step.now .nm { color: #031e23; }
    .us-step .wk { font-size: 11.5px; color: #8a949e; margin-top: 2px; min-height: 16px; }
    .us-note { margin-top: 16px; padding: 10px 14px; border-radius: 10px; background: var(--da-primary-soft); color: var(--da-primary-dark); font-size: 13.5px; display: flex; gap: 8px; align-items: center; }
    .us-empty { text-align: center; color: #9aa4ae; padding: 34px 0; font-size: 14px; }
    .us-empty i { font-size: 34px; display: block; margin-bottom: 6px; opacity: .6; }

    .us-card .table { margin-bottom: 0; }
    .us-card .table thead th { font-size: 11.5px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #8a949e; border-bottom: 1px solid #e8edf2; padding: 10px 12px; white-space: nowrap; }
    .us-card .table tbody td { padding: 13px 12px; font-size: 14px; border-color: #f0f3f6; }

    @media (max-width: 767px) {
        .us-stats { gap: 10px; }
        .us-stat { flex-direction: column; align-items: flex-start; gap: 10px; padding: 14px 12px; }
        .us-stat .lbl { font-size: 11.5px; }
        .us-card { padding: 16px; border-radius: 12px; }
        .us-order-total { text-align: left; }
        .us-step .wk { font-size: 10.5px; }
    }
</style>

<div class="us-head">
    <h5>Halo, <?= esc(session()->get('nama')) ?></h5>
    <p>Pantau status cucianmu di sini. Halaman ini hanya menampilkan cucian atas namamu.</p>
</div>

<div class="us-hint">
    <i class="bi bi-shield-lock"></i>
    <span>Username dan password awalmu dibuat otomatis oleh laundry. Sebaiknya ganti dengan yang mudah kamu ingat.</span>
    <a href="#" data-bs-toggle="modal" data-bs-target="#modalAkun">Ganti sekarang</a>
</div>

<?php if (! $pelanggan): ?>
<div class="us-card us-empty">
    <i class="bi bi-person-exclamation"></i>
    Akunmu belum terhubung ke data pelanggan. Hubungi pegawai laundry untuk dihubungkan.
</div>
<?php else: ?>

<div class="us-stats">
    <div class="us-card us-stat"><div class="us-ico brand"><i class="bi bi-bag"></i></div><div><div class="lbl">Total Cucian</div><div class="val"><?= $jumlah['total'] ?></div></div></div>
    <div class="us-card us-stat"><div class="us-ico orange"><i class="bi bi-hourglass-split"></i></div><div><div class="lbl">Sedang Dikerjakan</div><div class="val"><?= $jumlah['berjalan'] ?></div></div></div>
    <div class="us-card us-stat"><div class="us-ico green"><i class="bi bi-check2-circle"></i></div><div><div class="lbl">Selesai</div><div class="val"><?= $jumlah['selesai'] ?></div></div></div>
</div>

<div class="us-title">Cucian yang Sedang Berjalan</div>

<?php foreach ($berjalan as $c): $n = $langkah[$c['status']]; ?>
<div class="us-card us-order">
    <div class="us-order-top">
        <div>
            <div class="us-order-name"><?= esc($c['nama_layanan']) ?></div>
            <div class="us-order-meta"><?= esc($c['berat_kg']) ?> kg &middot; Masuk <?= $tgl($c['tanggal_masuk']) ?></div>
        </div>
        <div class="us-order-total">
            <div class="amt">Rp <?= number_format($c['total_harga'], 0, ',', '.') ?></div>
            <div class="id">Cucian #<?= (int) $c['id'] ?></div>
        </div>
    </div>

    <div class="us-steps">
        <div class="us-step <?= $n > 1 ? 'done' : 'now' ?>">
            <div class="us-dot"><i class="bi <?= $n > 1 ? 'bi-check-lg' : 'bi-inbox' ?>"></i></div>
            <div class="nm">Diterima</div>
            <div class="wk"><?= $tgl($c['tanggal_masuk']) ?></div>
        </div>
        <div class="us-step <?= $n > 2 ? 'done' : ($n === 2 ? 'now' : '') ?>">
            <div class="us-dot"><i class="bi <?= $n > 2 ? 'bi-check-lg' : 'bi-arrow-repeat' ?>"></i></div>
            <div class="nm">Diproses</div>
            <div class="wk"><?= $n === 2 ? 'Sedang berjalan' : '' ?></div>
        </div>
        <div class="us-step">
            <div class="us-dot"><i class="bi bi-bag-check"></i></div>
            <div class="nm">Selesai</div>
            <div class="wk"></div>
        </div>
    </div>

    <div class="us-note"><i class="bi bi-info-circle"></i> <?= esc($pesanStatus[$c['status']]) ?></div>
</div>
<?php endforeach; ?>

<?php if (empty($berjalan)): ?>
<div class="us-card us-empty" style="margin-bottom:14px;">
    <i class="bi bi-emoji-smile"></i>
    Tidak ada cucian yang sedang berjalan.
</div>
<?php endif; ?>

<div class="us-title" style="margin-top:22px;">Riwayat Cucian</div>
<div class="us-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr><th>Tgl Masuk</th><th>Layanan</th><th>Berat</th><th>Total</th><th>Status</th><th>Selesai Pada</th></tr>
            </thead>
            <tbody>
                <?php foreach ($cucian as $c): ?>
                <tr>
                    <td><?= $tgl($c['tanggal_masuk']) ?></td>
                    <td class="fw-medium"><?= esc($c['nama_layanan']) ?></td>
                    <td><?= esc($c['berat_kg']) ?> kg</td>
                    <td>Rp <?= number_format($c['total_harga'], 0, ',', '.') ?></td>
                    <td><span class="badge badge-status-<?= esc($c['status']) ?> text-capitalize"><?= esc($c['status']) ?></span></td>
                    <td><?= $c['tanggal_selesai'] ? esc($tglJam($c['tanggal_selesai'])) : '<span class="text-muted">-</span>' ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($cucian)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada riwayat cucian.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>

<?= $this->endSection() ?>
