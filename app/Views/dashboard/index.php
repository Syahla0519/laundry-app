<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card card-stat p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Cucian Sedang Diproses</div>
                    <div class="fs-3 fw-bold"><?= (int) $jumlah_diproses ?></div>
                </div>
                <div class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width:52px;height:52px;">
                    <i class="bi bi-hourglass-split fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-stat p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Pendapatan Hari Ini</div>
                    <div class="fs-5 fw-bold">Rp <?= number_format($pendapatan_hari_ini, 0, ',', '.') ?></div>
                </div>
                <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width:52px;height:52px;">
                    <i class="bi bi-cash-coin fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-stat p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Total Pelanggan</div>
                    <div class="fs-3 fw-bold"><?= (int) $total_pelanggan ?></div>
                </div>
                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:52px;height:52px;">
                    <i class="bi bi-people-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card card-soft p-3">
            <h6 class="fw-bold mb-3">Grafik Pendapatan 7 Hari Terakhir</h6>
            <canvas id="chartPendapatan" height="140"></canvas>
        </div>

        <div class="card card-soft p-3 mt-3">
            <h6 class="fw-bold mb-3">Transaksi Terbaru</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="text-muted small">
                        <tr><th>Pelanggan</th><th>Layanan</th><th>Berat</th><th>Total</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transaksi_terbaru as $t): ?>
                        <tr>
                            <td><?= esc($t['nama_pelanggan']) ?></td>
                            <td><?= esc($t['nama_layanan']) ?></td>
                            <td><?= esc($t['berat_kg']) ?> kg</td>
                            <td>Rp <?= number_format($t['total_harga'], 0, ',', '.') ?></td>
                            <td><span class="badge badge-status-<?= esc($t['status']) ?> text-capitalize"><?= esc($t['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($transaksi_terbaru)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-3">Belum ada transaksi.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card card-soft p-3">
            <h6 class="fw-bold mb-3">Layanan Paling Banyak Digunakan</h6>
            <?php foreach ($layanan_populer as $l): ?>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small"><?= esc($l['nama_layanan']) ?></span>
                <span class="badge bg-primary rounded-pill"><?= (int) $l['jumlah'] ?>x</span>
            </div>
            <?php endforeach; ?>
            <?php if (empty($layanan_populer)): ?>
            <p class="text-muted small mb-0">Belum ada data.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script>
const rekap = <?= json_encode($rekap_chart) ?>;
new Chart(document.getElementById('chartPendapatan'), {
    type: 'line',
    data: {
        labels: rekap.map(r => r.tanggal_masuk),
        datasets: [{
            label: 'Pendapatan (Rp)',
            data: rekap.map(r => r.total),
            borderColor: getComputedStyle(document.body).getPropertyValue('--da-primary').trim(),
            backgroundColor: 'rgba(' + getComputedStyle(document.body).getPropertyValue('--da-primary-rgb').trim() + ',.12)',
            tension: .35,
            fill: true,
        }]
    },
    options: { plugins: { legend: { display: false } } }
});
</script>
<?= $this->endSection() ?>
