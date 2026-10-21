<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="card card-soft p-3 mb-3">
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="?mulai=<?= date('Y-m-d') ?>&sampai=<?= date('Y-m-d') ?>" class="btn btn-sm btn-outline-primary">Hari Ini</a>
        <a href="?mulai=<?= date('Y-m-d', strtotime('-6 days')) ?>&sampai=<?= date('Y-m-d') ?>" class="btn btn-sm btn-outline-primary">Minggu Ini (7 hari)</a>
        <a href="?mulai=<?= date('Y-m-01') ?>&sampai=<?= date('Y-m-d') ?>" class="btn btn-sm btn-outline-primary">Bulan Ini</a>
    </div>
    <form method="get" class="row g-2 align-items-end">
        <div class="col-auto">
            <label class="form-label small fw-semibold">Dari Tanggal</label>
            <input type="date" name="mulai" class="form-control" value="<?= esc($mulai) ?>">
        </div>
        <div class="col-auto">
            <label class="form-label small fw-semibold">Sampai Tanggal</label>
            <input type="date" name="sampai" class="form-control" value="<?= esc($sampai) ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i> Filter Custom</button>
        </div>
        <div class="col-auto">
            <a class="btn btn-outline-danger" href="<?= site_url('laporan/export-pdf') ?>?mulai=<?= esc($mulai) ?>&sampai=<?= esc($sampai) ?>">
                <i class="bi bi-file-earmark-pdf-fill"></i> Export PDF
            </a>
        </div>
    </form>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card card-stat p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Pendapatan Hari Ini</div>
                    <div class="fs-5 fw-bold angka-pendapatan">Rp <?= number_format($pendapatan_hari_ini, 0, ',', '.') ?></div>
                </div>
                <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width:52px;height:52px;">
                    <i class="bi bi-cash-coin fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-stat p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Pendapatan Bulan Ini</div>
                    <div class="fs-5 fw-bold angka-pendapatan">Rp <?= number_format($pendapatan_bulan_ini, 0, ',', '.') ?></div>
                </div>
                <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width:52px;height:52px;">
                    <i class="bi bi-calendar3-range fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card card-soft p-3">
            <h6 class="fw-bold mb-3">Grafik Pendapatan (<?= esc($mulai) ?> s/d <?= esc($sampai) ?>)</h6>
            <canvas id="chartRekap" height="140"></canvas>
        </div>

        <div class="card card-soft p-3 mt-3">
            <h6 class="fw-bold mb-3">Tabel Rekap Pendapatan Harian</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead class="text-muted small"><tr><th>Tanggal</th><th>Jumlah Transaksi</th><th>Total Pendapatan</th></tr></thead>
                    <tbody>
                        <?php $grandTotal = 0; foreach ($rekap as $r): $grandTotal += $r['total']; ?>
                        <tr>
                            <td><?= esc($r['tanggal_masuk']) ?></td>
                            <td><?= (int) $r['jumlah_transaksi'] ?></td>
                            <td>Rp <?= number_format($r['total'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($rekap)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">Tidak ada transaksi pada rentang ini.</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold"><td colspan="2">Total</td><td>Rp <?= number_format($grandTotal, 0, ',', '.') ?></td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card card-soft p-3 mb-3">
            <h6 class="fw-bold mb-2">Cucian Sedang Diproses</h6>
            <div class="fs-3 fw-bold text-primary"><?= (int) $diproses ?></div>
            <p class="text-muted small mb-0">Total cucian berstatus diterima, diproses, atau selesai (belum diambil).</p>
        </div>

        <div class="card card-soft p-3">
            <h6 class="fw-bold mb-3">Layanan Paling Banyak Digunakan</h6>
            <?php foreach ($populer as $p): ?>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small"><?= esc($p['nama_layanan']) ?></span>
                <span class="badge bg-primary rounded-pill"><?= (int) $p['jumlah'] ?>x</span>
            </div>
            <?php endforeach; ?>
            <?php if (empty($populer)): ?>
            <p class="text-muted small mb-0">Belum ada data.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script>
const rekap = <?= json_encode($rekap) ?>;
new Chart(document.getElementById('chartRekap'), {
    type: 'bar',
    data: {
        labels: rekap.map(r => r.tanggal_masuk),
        datasets: [{ label: 'Pendapatan (Rp)', data: rekap.map(r => r.total), backgroundColor: '#7267e8', borderRadius: 6 }]
    },
    options: { plugins: { legend: { display: false } } }
});
</script>
<?= $this->endSection() ?>
