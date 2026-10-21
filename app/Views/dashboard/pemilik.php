<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php
// Hanya untuk tampilan: skala batang "layanan terpopuler"
$maksLayanan = !empty($layanan_populer)
    ? max(array_map(fn($l) => (int) $l['jumlah'], $layanan_populer))
    : 1;
$maksLayanan = max($maksLayanan, 1);
?>

<style>
    /* ===== Dashboard Pemilik (clean) — semua class diawali dp- ===== */
    .dp-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
    .dp-head h5 { margin: 0 0 2px; font-size: 20px; font-weight: 700; color: #031e23; }
    .dp-head p { margin: 0; font-size: 14px; color: #6c757d; }
    .dp-head p strong { color: #031e23; font-weight: 600; }

    .dp-card { background: #fff; border: 1px solid #e8edf2; border-radius: 14px; padding: 20px; }
    .dp-card-title { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; margin-bottom: 16px; }
    .dp-card-title h6 { margin: 0; font-size: 15px; font-weight: 600; color: #031e23; }
    .dp-card-title span, .dp-card-title a { font-size: 12.5px; color: #8a949e; text-decoration: none; }
    .dp-card-title a { color: var(--da-primary); font-weight: 500; }

    /* Kartu ringkasan */
    .dp-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 16px; }
    .dp-stat { display: flex; align-items: center; gap: 14px; padding: 18px; min-width: 0; }
    .dp-ico { flex: 0 0 44px; width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
    .dp-ico.orange { background: #fff3e3; color: #f08a1c; }
    .dp-ico.green  { background: #e5f7ea; color: #1f9d4c; }
    .dp-ico.brand  { background: var(--da-primary-soft); color: var(--da-primary); }
    .dp-ico.slate  { background: #edf0f4; color: #5b6b7f; }
    .dp-stat .dp-label { font-size: 12.5px; color: #8a949e; margin-bottom: 2px; }
    .dp-stat .dp-value { font-size: clamp(17px, 4.8vw, 22px); font-weight: 700; color: #031e23; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .dp-stat > div:last-child { min-width: 0; }

    /* Layanan terpopuler */
    .dp-rank { padding: 10px 0; border-bottom: 1px solid #f0f3f6; }
    .dp-rank:last-child { border-bottom: 0; padding-bottom: 0; }
    .dp-rank-top { display: flex; align-items: center; gap: 10px; margin-bottom: 7px; font-size: 14px; }
    .dp-rank-no { width: 22px; height: 22px; border-radius: 50%; background: #f0f3f6; color: #5b6b7f; font-size: 11.5px; font-weight: 600; display: flex; align-items: center; justify-content: center; flex: 0 0 22px; }
    .dp-rank-name { flex: 1; font-weight: 500; color: #031e23; min-width: 0; }
    .dp-rank-count { font-size: 12.5px; color: #6c757d; white-space: nowrap; }
    .dp-bar { height: 6px; border-radius: 99px; background: #f0f3f6; overflow: hidden; }
    .dp-bar > i { display: block; height: 100%; border-radius: 99px; background: var(--da-primary); }


    /* Stok menipis */
    .dp-stock { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 9px 0; border-bottom: 1px solid #f0f3f6; font-size: 14px; }
    .dp-stock:last-of-type { border-bottom: 0; }
    .dp-stock-name { font-weight: 500; color: #031e23; min-width: 0; }
    .dp-stock-val { font-size: 12.5px; font-weight: 600; white-space: nowrap; padding: 3px 10px; border-radius: 99px; background: #fff1de; color: #c76a00; }
    .dp-stock-val.habis { background: #ffe3e3; color: #d12b2b; }

    /* Akses cepat */
    .dp-link { display: flex; align-items: center; gap: 12px; padding: 11px 4px; color: #031e23; text-decoration: none; font-size: 14px; font-weight: 500; border-bottom: 1px solid #f0f3f6; }
    .dp-link:last-child { border-bottom: 0; padding-bottom: 0; }
    .dp-link i:first-child { width: 32px; height: 32px; border-radius: 9px; background: var(--da-primary-soft); color: var(--da-primary); display: flex; align-items: center; justify-content: center; font-size: 15px; flex: 0 0 32px; }
    .dp-link i:last-child { margin-left: auto; color: #b3bcc5; font-size: 13px; }
    .dp-link:hover { color: var(--da-primary); }

    /* Tabel transaksi terbaru */
    .dp-card .table { margin-bottom: 0; }
    .dp-card .table thead th { font-size: 11.5px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #8a949e; border-bottom: 1px solid #e8edf2; padding: 10px 12px; white-space: nowrap; }
    .dp-card .table tbody td { padding: 13px 12px; font-size: 14px; border-color: #f0f3f6; }
    .dp-card .table tbody tr:last-child td { border-bottom: 0; }
    .dp-card .table-hover > tbody > tr:hover { background: #fafbfc; }

    @media (max-width: 1199px) { .dp-stats { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 575px)  {
        .dp-stats { gap: 10px; }
        .dp-stat { padding: 14px; gap: 10px; flex-direction: column; align-items: flex-start; }
        .dp-card { padding: 16px; border-radius: 12px; }
    }
</style>

<!-- Sapaan ringkas -->
<div class="dp-head">
    <div>
        <h5>Halo, <?= esc(session()->get('nama')) ?></h5>
        <p>Pendapatan hari ini <strong>Rp <?= number_format($pendapatan_hari_ini, 0, ',', '.') ?></strong>
            &middot; <strong><?= (int) $jumlah_diproses ?></strong> cucian sedang diproses</p>
    </div>
    <a href="<?= site_url('laporan') ?>" class="btn btn-primary btn-sm px-3"><i class="bi bi-graph-up-arrow me-1"></i> Laporan &amp; Rekap</a>
</div>

<!-- Ringkasan -->
<div class="dp-stats">
    <div class="dp-card dp-stat">
        <div class="dp-ico orange"><i class="bi bi-hourglass-split"></i></div>
        <div>
            <div class="dp-label">Sedang Diproses</div>
            <div class="dp-value"><?= (int) $jumlah_diproses ?></div>
        </div>
    </div>
    <div class="dp-card dp-stat">
        <div class="dp-ico green"><i class="bi bi-cash-coin"></i></div>
        <div>
            <div class="dp-label">Pendapatan Hari Ini</div>
            <div class="dp-value">Rp <?= number_format($pendapatan_hari_ini, 0, ',', '.') ?></div>
        </div>
    </div>
    <div class="dp-card dp-stat">
        <div class="dp-ico brand"><i class="bi bi-calendar3-range"></i></div>
        <div>
            <div class="dp-label">Pendapatan Bulan Ini</div>
            <div class="dp-value">Rp <?= number_format($pendapatan_bulan_ini, 0, ',', '.') ?></div>
        </div>
    </div>
    <div class="dp-card dp-stat">
        <div class="dp-ico slate"><i class="bi bi-people"></i></div>
        <div>
            <div class="dp-label">Total Pelanggan</div>
            <div class="dp-value"><?= (int) $total_pelanggan ?></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="dp-card h-100">
            <div class="dp-card-title">
                <h6>Pendapatan</h6>
                <span>7 hari terakhir</span>
            </div>
            <canvas id="chartPendapatan" height="140"></canvas>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="dp-card mb-3">
            <div class="dp-card-title">
                <h6>Layanan Terpopuler</h6>
            </div>
            <?php foreach ($layanan_populer as $i => $l): ?>
            <div class="dp-rank">
                <div class="dp-rank-top">
                    <span class="dp-rank-no"><?= $i + 1 ?></span>
                    <span class="dp-rank-name"><?= esc($l['nama_layanan']) ?></span>
                    <span class="dp-rank-count"><?= (int) $l['jumlah'] ?>x</span>
                </div>
                <div class="dp-bar"><i style="width: <?= round(((int) $l['jumlah'] / $maksLayanan) * 100) ?>%"></i></div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($layanan_populer)): ?>
            <p class="text-secondary mb-0" style="font-size:14px;">Belum ada data.</p>
            <?php endif; ?>
        </div>

        <?php if (!empty($stok_menipis)): ?>
        <div class="dp-card mb-3">
            <div class="dp-card-title">
                <h6><i class="bi bi-exclamation-triangle me-1" style="color:#f08a1c"></i> Stok Menipis</h6>
                <a href="<?= site_url('barang') ?>">Kelola stok</a>
            </div>
            <?php foreach ($stok_menipis as $b): ?>
            <div class="dp-stock">
                <span class="dp-stock-name"><?= esc($b['nama_barang']) ?></span>
                <span class="dp-stock-val <?= (int) $b['stok'] === 0 ? 'habis' : '' ?>"><?= (int) $b['stok'] === 0 ? 'Habis' : (int) $b['stok'] . ' ' . esc($b['satuan']) ?> <span style="font-weight:400;opacity:.75">/ min <?= (int) $b['stok_minimum'] ?></span></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="dp-card">
            <div class="dp-card-title">
                <h6>Akses Cepat</h6>
            </div>
            <a href="<?= site_url('layanan') ?>" class="dp-link"><i class="bi bi-basket"></i> Atur Harga Layanan <i class="bi bi-chevron-right"></i></a>
            <a href="<?= site_url('barang') ?>" class="dp-link"><i class="bi bi-box-seam"></i> Stok Barang <i class="bi bi-chevron-right"></i></a>
            <a href="<?= site_url('log-aktivitas') ?>" class="dp-link"><i class="bi bi-journal-text"></i> Log Aktivitas <i class="bi bi-chevron-right"></i></a>
            <a href="<?= site_url('laporan') ?>" class="dp-link"><i class="bi bi-graph-up-arrow"></i> Laporan &amp; Rekap Lengkap <i class="bi bi-chevron-right"></i></a>
            <a href="<?= site_url('laporan/export-pdf') ?>" class="dp-link"><i class="bi bi-file-earmark-pdf"></i> Export PDF (7 Hari Terakhir) <i class="bi bi-chevron-right"></i></a>
        </div>
    </div>
</div>

<div class="dp-card mb-3">
    <div class="dp-card-title">
        <h6>Transaksi Terbaru</h6>
        <a href="<?= site_url('cucian') ?>">Lihat semua</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr><th>Pelanggan</th><th>Layanan</th><th>Berat</th><th>Total</th><th>Status</th><th>Dicatat Oleh</th><th>Diselesaikan Oleh</th></tr>
            </thead>
            <tbody>
                <?php foreach ($transaksi_terbaru as $t): ?>
                <tr>
                    <td class="fw-medium"><?= esc($t['nama_pelanggan']) ?></td>
                    <td><?= esc($t['nama_layanan']) ?></td>
                    <td><?= esc($t['berat_kg']) ?> kg</td>
                    <td>Rp <?= number_format($t['total_harga'], 0, ',', '.') ?></td>
                    <td><span class="badge badge-status-<?= esc($t['status']) ?> text-capitalize"><?= esc($t['status']) ?></span></td>
                    <td><?= esc($t['nama_pencatat'] ?? '-') ?></td>
                    <td><?= esc($t['nama_penyelesai'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($transaksi_terbaru)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada transaksi.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script>
const rekap = <?= json_encode($rekap_chart) ?>;
const warna = getComputedStyle(document.body).getPropertyValue('--da-primary').trim();
const warnaRgb = getComputedStyle(document.body).getPropertyValue('--da-primary-rgb').trim();
const rupiah = v => 'Rp ' + Number(v).toLocaleString('id-ID');

new Chart(document.getElementById('chartPendapatan'), {
    type: 'line',
    data: {
        labels: rekap.map(r => r.tanggal_masuk),
        datasets: [{
            label: 'Pendapatan',
            data: rekap.map(r => r.total),
            borderColor: warna,
            backgroundColor: 'rgba(' + warnaRgb + ',.08)',
            borderWidth: 2,
            pointRadius: 3,
            pointBackgroundColor: '#fff',
            pointBorderColor: warna,
            pointBorderWidth: 2,
            tension: .35,
            fill: true,
        }]
    },
    options: {
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => rupiah(ctx.parsed.y) } }
        },
        scales: {
            x: { grid: { display: false }, ticks: { color: '#8a949e', font: { size: 11 } } },
            y: {
                beginAtZero: true,
                suggestedMax: 10000,
                grid: { color: '#eef1f4' },
                border: { display: false },
                ticks: { color: '#8a949e', font: { size: 11 }, callback: v => rupiah(v) }
            }
        }
    }
});
</script>
<?= $this->endSection() ?>
