<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php
$jumlahAntrian = count($cucian_diterima) + count($cucian_diproses);
?>

<style>
    /* ===== Dashboard Pegawai (clean) — semua class diawali dp- ===== */
    .dp-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
    .dp-head h5 { margin: 0 0 2px; font-size: 20px; font-weight: 700; color: #031e23; }
    .dp-head p { margin: 0; font-size: 14px; color: #6c757d; }
    .dp-head p strong { color: #031e23; font-weight: 600; }

    .dp-card { background: #fff; border: 1px solid #e8edf2; border-radius: 14px; padding: 20px; }
    .dp-card-title { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; margin-bottom: 16px; }
    .dp-card-title h6 { margin: 0; font-size: 15px; font-weight: 600; color: #031e23; }

    /* Kartu ringkasan */
    .dp-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 16px; }
    .dp-stat { display: flex; align-items: center; gap: 14px; padding: 18px; min-width: 0; }
    .dp-ico { flex: 0 0 44px; width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
    .dp-ico.orange { background: #fff3e3; color: #f08a1c; }
    .dp-ico.green  { background: #e5f7ea; color: #1f9d4c; }
    .dp-ico.brand  { background: var(--da-primary-soft); color: var(--da-primary); }
    .dp-ico.slate  { background: #edf0f4; color: #5b6b7f; }
    .dp-stat .dp-label { font-size: 12.5px; color: #8a949e; margin-bottom: 2px; }
    .dp-stat .dp-value { font-size: clamp(18px, 5vw, 24px); font-weight: 700; color: #031e23; line-height: 1.2; }
    .dp-stat > div:last-child { min-width: 0; }


    /* Peringatan stok menipis */
    .dp-warn { display: flex; align-items: flex-start; gap: 12px; padding: 14px 16px; margin-bottom: 16px; background: #fff8ec; border: 1px solid #ffe2b8; border-radius: 12px; font-size: 13.5px; color: #7a4a00; }
    .dp-warn > i { font-size: 18px; color: #f08a1c; margin-top: 1px; }
    .dp-warn strong { color: #5c3700; }
    .dp-warn a { color: #7a4a00; font-weight: 600; }

    /* Alur pengerjaan (3 kolom) */
    .dp-section-title { font-size: 15px; font-weight: 600; color: #031e23; margin: 8px 0 12px; }
    .dp-col-head { display: flex; align-items: center; gap: 8px; padding-bottom: 14px; margin-bottom: 4px; border-bottom: 1px solid #f0f3f6; }
    .dp-dot { width: 9px; height: 9px; border-radius: 50%; flex: 0 0 9px; }
    .dp-dot.diterima { background: #ff9b3d; } .dp-dot.diproses { background: #1b00ff; } .dp-dot.selesai { background: #09cc06; }
    .dp-col-head h6 { margin: 0; font-size: 14.5px; font-weight: 600; color: #031e23; flex: 1; }
    .dp-count { min-width: 24px; height: 22px; padding: 0 8px; border-radius: 99px; background: #f0f3f6; color: #5b6b7f; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; }
    .dp-col-body { max-height: 440px; overflow-y: auto; }

    .dp-task { display: flex; gap: 12px; padding: 14px 0; border-bottom: 1px solid #f0f3f6; }
    .dp-task:last-child { border-bottom: 0; }
    .dp-av { flex: 0 0 36px; width: 36px; height: 36px; border-radius: 10px; font-size: 14px; font-weight: 700; display: flex; align-items: center; justify-content: center; }
    .dp-av.diterima { background: #fff1de; color: #c76a00; }
    .dp-av.diproses { background: #e0e5ff; color: #1b00ff; }
    .dp-av.selesai  { background: #d9f7d8; color: #0a8c07; }
    .dp-task-name { font-size: 14px; font-weight: 600; color: #031e23; line-height: 1.3; }
    .dp-task-meta { font-size: 12.5px; color: #6c757d; margin-top: 2px; }
    .dp-task-sub  { font-size: 12px; color: #9aa4ae; margin-top: 2px; }
    .dp-btn-done { margin-top: 10px; display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; font-size: 12.5px; font-weight: 500; color: #0a8c07; background: #eefaed; border: 1px solid #cdeccb; border-radius: 8px; cursor: pointer; }
    .dp-btn-done:hover { background: #09cc06; border-color: #09cc06; color: #fff; }
    .dp-empty { text-align: center; color: #9aa4ae; font-size: 13.5px; padding: 28px 0; }
    .dp-empty i { display: block; font-size: 26px; margin-bottom: 6px; opacity: .6; }

    /* Akses cepat */
    .dp-quick { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    .dp-tile { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border: 1px solid #e8edf2; border-radius: 12px; color: #031e23; text-decoration: none; font-size: 14px; font-weight: 500; }
    .dp-tile i:first-child { width: 34px; height: 34px; border-radius: 9px; background: var(--da-primary-soft); color: var(--da-primary); display: flex; align-items: center; justify-content: center; font-size: 16px; flex: 0 0 34px; }
    .dp-tile i:last-child { margin-left: auto; color: #b3bcc5; font-size: 13px; }
    .dp-tile:hover { border-color: var(--da-primary); color: var(--da-primary); }

    @media (max-width: 991px) { .dp-quick { grid-template-columns: 1fr; } }
    @media (max-width: 767px) {
        .dp-stats { grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .dp-stat { padding: 14px 12px; flex-direction: column; align-items: flex-start; gap: 10px; }
        .dp-stat .dp-label { font-size: 11.5px; }
        .dp-card { padding: 16px; border-radius: 12px; }
        .dp-col-body { max-height: none; }
    }
</style>

<!-- Sapaan ringkas -->
<div class="dp-head">
    <div>
        <h5>Halo, <?= esc(session()->get('nama')) ?></h5>
        <p>Ada <strong><?= $jumlahAntrian ?></strong> cucian yang menunggu dikerjakan.</p>
    </div>
    <a href="<?= site_url('cucian') ?>" class="btn btn-primary btn-sm px-3"><i class="bi bi-plus-lg me-1"></i> Terima Cucian Baru</a>
</div>

<!-- Ringkasan -->
<div class="dp-stats">
    <div class="dp-card dp-stat">
        <div class="dp-ico brand"><i class="bi bi-calendar-check"></i></div>
        <div>
            <div class="dp-label">Masuk Hari Ini</div>
            <div class="dp-value"><?= (int) $jumlah_hari_ini ?></div>
        </div>
    </div>
    <div class="dp-card dp-stat">
        <div class="dp-ico orange"><i class="bi bi-hourglass-split"></i></div>
        <div>
            <div class="dp-label">Sedang Diproses</div>
            <div class="dp-value"><?= $jumlahAntrian ?></div>
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

<?php if (!empty($stok_menipis)): ?>
<div class="dp-warn">
    <i class="bi bi-exclamation-triangle"></i>
    <div>
        <strong>Stok barang menipis.</strong>
        <?php
        $teks = array_map(fn ($b) => esc($b['nama_barang']) . ' (' . ((int) $b['stok'] === 0 ? 'habis' : (int) $b['stok'] . ' ' . esc($b['satuan'])) . ')', $stok_menipis);
        echo implode(', ', $teks);
        ?>.
        Beri tahu pemilik untuk menambah stok, atau <a href="<?= site_url('barang') ?>">lihat stok barang</a>.
    </div>
</div>
<?php endif; ?>

<div class="dp-section-title">Alur Pengerjaan Cucian</div>

<div class="row g-3 mb-3">
    <?php
    $kolom = [
        ['judul' => 'Diterima', 'data' => $cucian_diterima, 'kelas' => 'diterima'],
        ['judul' => 'Diproses', 'data' => $cucian_diproses, 'kelas' => 'diproses'],
        ['judul' => 'Selesai',  'data' => $cucian_selesai,  'kelas' => 'selesai'],
    ];
    ?>
    <?php foreach ($kolom as $k): ?>
    <div class="col-lg-4">
        <div class="dp-card h-100">
            <div class="dp-col-head">
                <span class="dp-dot <?= $k['kelas'] ?>"></span>
                <h6><?= $k['judul'] ?></h6>
                <span class="dp-count"><?= count($k['data']) ?></span>
            </div>
            <div class="dp-col-body">
                <?php foreach ($k['data'] as $c): ?>
                <div class="dp-task">
                    <div class="dp-av <?= $k['kelas'] ?>"><?= esc(strtoupper(substr($c['nama_pelanggan'], 0, 1))) ?></div>
                    <div class="flex-grow-1" style="min-width:0;">
                        <div class="dp-task-name"><?= esc($c['nama_pelanggan']) ?></div>
                        <div class="dp-task-meta"><?= esc($c['nama_layanan']) ?> &middot; <?= esc($c['berat_kg']) ?> kg</div>
                        <div class="dp-task-sub">Masuk <?= esc($c['tanggal_masuk']) ?></div>
                        <?php if ($k['kelas'] === 'diproses'): ?>
                        <button type="button" class="dp-btn-done" onclick="selesaikanCucian(<?= $c['id'] ?>)">
                            <i class="bi bi-check-circle"></i> Tandai Selesai
                        </button>
                        <?php elseif ($k['kelas'] === 'selesai'): ?>
                        <div class="dp-task-sub"><i class="bi bi-person-check me-1"></i>Oleh <?= esc($c['nama_penyelesai'] ?? '-') ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($k['data'])): ?>
                <div class="dp-empty">
                    <i class="bi bi-inbox"></i>
                    Tidak ada cucian di tahap ini.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="dp-card mb-3">
    <div class="dp-card-title">
        <h6>Akses Cepat</h6>
    </div>
    <div class="dp-quick">
        <a href="<?= site_url('cucian') ?>" class="dp-tile"><i class="bi bi-clipboard2-check"></i> Kelola Transaksi Cucian <i class="bi bi-chevron-right"></i></a>
        <a href="<?= site_url('pelanggan') ?>" class="dp-tile"><i class="bi bi-person-plus"></i> Tambah Pelanggan Baru <i class="bi bi-chevron-right"></i></a>
        <a href="<?= site_url('layanan') ?>" class="dp-tile"><i class="bi bi-list-check"></i> Daftar Harga Layanan <i class="bi bi-chevron-right"></i></a>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script>
const csrfName = '<?= csrf_token() ?>';
const csrfHash = '<?= csrf_hash() ?>';

function selesaikanCucian(id) {
    const fd = new FormData();
    fd.append(csrfName, csrfHash);
    fd.append('status', 'selesai');
    fetch(`<?= site_url('cucian/status') ?>/${id}`, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            tampilPesan(res.status, res.message);
            if (res.status === 'berhasil') {
                setTimeout(() => location.reload(), 900);
            }
        });
}
</script>
<?= $this->endSection() ?>
