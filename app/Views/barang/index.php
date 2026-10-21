<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php $isPemilik = session()->get('role') === 'pemilik'; ?>

<style>
    .br-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 16px; }
    .br-stat { background: #fff; border: 1px solid #e8edf2; border-radius: 14px; padding: 16px 18px; display: flex; align-items: center; gap: 14px; }
    .br-ico { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex: 0 0 44px; }
    .br-ico.brand { background: var(--da-primary-soft); color: var(--da-primary); }
    .br-ico.warn  { background: #fff3e3; color: #f08a1c; }
    .br-ico.green { background: #e5f7ea; color: #1f9d4c; }
    .br-stat .lbl { font-size: 12.5px; color: #8a949e; }
    .br-stat .val { font-size: 22px; font-weight: 700; color: #031e23; line-height: 1.2; }
    .badge-stok-ok     { background: #e5f7ea; color: #1f9d4c; }
    .badge-stok-menipis{ background: #fff0dc; color: #c76a00; }
    .badge-stok-habis  { background: #fde4e4; color: #c0392b; }
    .pill-pakai { display: inline-block; background: #f0f3f6; color: #5b6b7f; border-radius: 99px; padding: 2px 10px; font-size: 12px; margin: 0 4px 4px 0; white-space: nowrap; }
    @media (max-width: 767px) { .br-stats { grid-template-columns: 1fr; gap: 10px; } }
</style>

<div class="br-stats">
    <div class="br-stat"><div class="br-ico brand"><i class="bi bi-box-seam"></i></div><div><div class="lbl">Jenis Barang</div><div class="val" id="statJenis">-</div></div></div>
    <div class="br-stat"><div class="br-ico warn"><i class="bi bi-exclamation-triangle"></i></div><div><div class="lbl">Stok Menipis</div><div class="val" id="statMenipis">-</div></div></div>
    <div class="br-stat"><div class="br-ico green"><i class="bi bi-stack"></i></div><div><div class="lbl">Total Unit Tersedia</div><div class="val" id="statTotal">-</div></div></div>
</div>

<div class="card card-soft p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">Daftar Stok Barang</h6>
        <?php if ($isPemilik): ?>
        <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalBarang" onclick="bukaTambah()">
            <i class="bi bi-plus-lg"></i> Tambah Barang
        </button>
        <?php else: ?>
        <span class="badge badge-role">Hanya lihat — stok diatur oleh pemilik</span>
        <?php endif; ?>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="text-muted small">
                <tr><th>#</th><th>Barang</th><th>Stok</th><th>Batas Min.</th><th>Status</th><th>Dipakai di Layanan</th><?php if ($isPemilik): ?><th class="text-end">Aksi</th><?php endif; ?></tr>
            </thead>
            <tbody id="tabelBarang"></tbody>
        </table>
    </div>
    <div class="small text-muted mt-2">Stok berkurang otomatis saat cucian dicatat (sesuai paket barang layanannya) dan dikembalikan bila cucian dihapus.</div>
</div>

<div class="modal fade" id="modalBarang" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formBarang">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold" id="judulModalBarang">Tambah Barang</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="barang_id">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Barang</label>
                        <input type="text" class="form-control" name="nama_barang" id="barang_nama" placeholder="Plastik Kemasan / Hanger / Deterjen" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-semibold">Satuan</label>
                            <input type="text" class="form-control" name="satuan" id="barang_satuan" placeholder="pcs / sachet / botol" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-semibold">Stok Saat Ini</label>
                            <input type="number" min="0" step="1" class="form-control" name="stok" id="barang_stok" required>
                        </div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label small fw-semibold">Batas Stok Minimum</label>
                        <input type="number" min="0" step="1" class="form-control" name="stok_minimum" id="barang_min" value="10" required>
                        <div class="form-text">Stok menipis ditandai bila jumlahnya sama dengan atau di bawah angka ini.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script>
const baseUrl = '<?= site_url() ?>';
const csrfName = '<?= csrf_token() ?>';
let csrfHash = '<?= csrf_hash() ?>';
const isPemilik = <?= $isPemilik ? 'true' : 'false' ?>;
const modalBarang = new bootstrap.Modal(document.getElementById('modalBarang'));
let dataBarang = {}; // cache baris tabel (id -> data)

function esc(t) {
    return String(t ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function bukaTambah() {
    document.getElementById('formBarang').reset();
    document.getElementById('barang_id').value = '';
    document.getElementById('barang_min').value = 10;
    document.getElementById('judulModalBarang').innerText = 'Tambah Barang';
}

function badgeStok(row) {
    if (Number(row.stok) <= 0) return '<span class="badge badge-stok-habis">Habis</span>';
    if (row.menipis) return '<span class="badge badge-stok-menipis">Menipis</span>';
    return '<span class="badge badge-stok-ok">Aman</span>';
}

function muatTabel() {
    fetch(`${baseUrl}/barang/data`)
        .then(r => r.json())
        .then(res => {
            const tbody = document.getElementById('tabelBarang');
            tbody.innerHTML = '';
            let menipis = 0, total = 0;
            dataBarang = {};

            res.data.forEach((row, i) => {
                dataBarang[row.id] = row;
                if (row.menipis) menipis++;
                total += Number(row.stok);
                const pakai = row.dipakai_di.length
                    ? row.dipakai_di.map(t => `<span class="pill-pakai">${esc(t)}</span>`).join('')
                    : '<span class="text-muted">Belum dipakai</span>';
                const aksi = isPemilik ? `
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-success" onclick="restok(${row.id})" title="Tambah stok"><i class="bi bi-plus-lg"></i> Stok</button>
                        <button class="btn btn-sm btn-outline-primary" onclick="bukaEdit(${row.id})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="hapusData(${row.id})"><i class="bi bi-trash"></i></button>
                    </td>` : '';
                tbody.innerHTML += `
                <tr>
                    <td>${i + 1}</td>
                    <td class="fw-medium">${esc(row.nama_barang)}</td>
                    <td>${Number(row.stok).toLocaleString('id-ID')} ${esc(row.satuan)}</td>
                    <td>${row.stok_minimum}</td>
                    <td>${badgeStok(row)}</td>
                    <td>${pakai}</td>
                    ${aksi}
                </tr>`;
            });

            if (res.data.length === 0) {
                const colspan = isPemilik ? 7 : 6;
                tbody.innerHTML = `<tr><td colspan="${colspan}" class="text-center text-muted py-3">Belum ada data barang.</td></tr>`;
            }
            document.getElementById('statJenis').innerText = res.data.length;
            document.getElementById('statMenipis').innerText = menipis;
            document.getElementById('statTotal').innerText = total.toLocaleString('id-ID');
        });
}

function bukaEdit(id) {
    fetch(`${baseUrl}/barang/edit/${id}`)
        .then(r => r.json())
        .then(res => {
            if (res.status !== 'berhasil') return tampilPesan('gagal', res.message);
            document.getElementById('judulModalBarang').innerText = 'Edit Barang';
            document.getElementById('barang_id').value = res.data.id;
            document.getElementById('barang_nama').value = res.data.nama_barang;
            document.getElementById('barang_satuan').value = res.data.satuan;
            document.getElementById('barang_stok').value = res.data.stok;
            document.getElementById('barang_min').value = res.data.stok_minimum;
            modalBarang.show();
        });
}

function restok(id) {
    Swal.fire({
        title: 'Tambah stok',
        text: dataBarang[id]?.nama_barang ?? '',
        input: 'number',
        inputAttributes: { min: 1, step: 1 },
        inputPlaceholder: 'Jumlah yang ditambahkan',
        showCancelButton: true,
        confirmButtonText: 'Tambah',
        cancelButtonText: 'Batal',
    }).then(result => {
        if (!result.isConfirmed) return;
        const fd = new FormData();
        fd.append(csrfName, csrfHash);
        fd.append('jumlah', result.value);
        fetch(`${baseUrl}/barang/tambah-stok/${id}`, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => { tampilPesan(res.status, res.message); muatTabel(); });
    });
}

function hapusData(id) {
    konfirmasiHapus(null, () => {
        const fd = new FormData();
        fd.append(csrfName, csrfHash);
        fetch(`${baseUrl}/barang/hapus/${id}`, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => { tampilPesan(res.status, res.message); muatTabel(); });
    });
}

document.getElementById('formBarang').addEventListener('submit', function (e) {
    e.preventDefault();
    const fd = new FormData(this);
    fd.append(csrfName, csrfHash);
    fetch(`${baseUrl}/barang/simpan`, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'berhasil') { modalBarang.hide(); muatTabel(); }
            tampilPesan(res.status, res.status === 'berhasil' ? res.message : (res.message + ' ' + Object.values(res.errors ?? {}).join(' ')));
        });
});

muatTabel();
</script>
<?= $this->endSection() ?>
