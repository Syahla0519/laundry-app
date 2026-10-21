<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<style>
    .pill-pakai { display: inline-block; background: #f0f3f6; color: #5b6b7f; border-radius: 99px; padding: 2px 10px; font-size: 12px; margin: 0 4px 4px 0; white-space: nowrap; }
</style>

<div class="card card-soft p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">Daftar Layanan</h6>
        <?php if (session()->get('role') === 'pemilik'): ?>
        <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalLayanan" onclick="bukaTambah()">
            <i class="bi bi-plus-lg"></i> Tambah Layanan
        </button>
        <?php else: ?>
        <span class="badge badge-role">Hanya lihat — harga diatur oleh pemilik</span>
        <?php endif; ?>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="text-muted small">
                <tr><th>#</th><th>Nama Layanan</th><th>Harga / Kg</th><th>Paket Barang per Cucian</th><?php if (session()->get('role') === 'pemilik'): ?><th class="text-end">Aksi</th><?php endif; ?></tr>
            </thead>
            <tbody id="tabelLayanan"></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalLayanan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formLayanan">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold" id="judulModalLayanan">Tambah Layanan</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="layanan_id">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Layanan</label>
                        <input type="text" class="form-control" name="nama_layanan" id="layanan_nama" placeholder="Cuci Kering / Cuci Setrika / Setrika Saja" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Harga per Kg (Rp)</label>
                        <input type="number" step="0.01" class="form-control" name="harga_per_kg" id="layanan_harga" required>
                    </div>
                    <div class="mb-1">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label small fw-semibold mb-0">Paket Barang per Cucian</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="tambahBarisPaket()"><i class="bi bi-plus-lg"></i> Barang</button>
                        </div>
                        <div id="paketWrap"></div>
                        <div class="form-text" id="paketKosong">Belum ada barang. Barang di sini akan otomatis mengurangi stok setiap kali ada cucian dengan layanan ini.</div>
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
const isPemilik = <?= session()->get('role') === 'pemilik' ? 'true' : 'false' ?>;
const modalLayanan = new bootstrap.Modal(document.getElementById('modalLayanan'));
const daftarBarang = <?= json_encode(array_map(fn ($b) => ['id' => (int) $b['id'], 'nama' => $b['nama_barang'], 'satuan' => $b['satuan']], $barang)) ?>;

function esc(t) {
    return String(t ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

/** Satu baris paket: pilih barang + jumlah + tombol hapus */
function tambahBarisPaket(barangId = '', jumlah = 1) {
    const opsi = daftarBarang.map(b => `<option value="${b.id}" ${String(b.id) === String(barangId) ? 'selected' : ''}>${esc(b.nama)} (${esc(b.satuan)})</option>`).join('');
    const div = document.createElement('div');
    div.className = 'd-flex gap-2 mb-2 paket-baris';
    div.innerHTML = `
        <select class="form-select form-select-sm" name="barang_id[]" required>
            <option value="">-- Pilih barang --</option>${opsi}
        </select>
        <input type="number" min="1" step="1" class="form-control form-control-sm" style="max-width:90px;" name="jumlah[]" value="${jumlah}" required>
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.parentElement.remove(); cekPaketKosong();"><i class="bi bi-x-lg"></i></button>`;
    document.getElementById('paketWrap').appendChild(div);
    cekPaketKosong();
}

function cekPaketKosong() {
    document.getElementById('paketKosong').style.display = document.querySelectorAll('.paket-baris').length ? 'none' : '';
}

function isiPaket(paket) {
    document.getElementById('paketWrap').innerHTML = '';
    (paket || []).forEach(p => tambahBarisPaket(p.barang_id, p.jumlah));
    cekPaketKosong();
}

function bukaTambah() {
    document.getElementById('formLayanan').reset();
    document.getElementById('layanan_id').value = '';
    document.getElementById('judulModalLayanan').innerText = 'Tambah Layanan';
    isiPaket([]);
}

function muatTabel() {
    fetch(`${baseUrl}/layanan/data`)
        .then(r => r.json())
        .then(res => {
            const tbody = document.getElementById('tabelLayanan');
            tbody.innerHTML = '';
            res.data.forEach((row, i) => {
                const paketHtml = row.paket.length
                    ? row.paket.map(p => `<span class="pill-pakai">${esc(p.nama_barang)} ×${p.jumlah}</span>`).join('')
                    : '<span class="text-muted">-</span>';
                const kolomAksi = isPemilik ? `
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary" onclick="bukaEdit(${row.id})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="hapusData(${row.id})"><i class="bi bi-trash"></i></button>
                    </td>` : '';
                tbody.innerHTML += `
                <tr>
                    <td>${i + 1}</td>
                    <td>${esc(row.nama_layanan)}</td>
                    <td>Rp ${Number(row.harga_per_kg).toLocaleString('id-ID')}</td>
                    <td>${paketHtml}</td>
                    ${kolomAksi}
                </tr>`;
            });
            if (res.data.length === 0) {
                const colspan = isPemilik ? 5 : 4;
                tbody.innerHTML = `<tr><td colspan="${colspan}" class="text-center text-muted py-3">Belum ada data layanan.</td></tr>`;
            }
        });
}

function bukaEdit(id) {
    fetch(`${baseUrl}/layanan/edit/${id}`)
        .then(r => r.json())
        .then(res => {
            if (res.status !== 'berhasil') return tampilPesan('gagal', res.message);
            document.getElementById('judulModalLayanan').innerText = 'Edit Layanan';
            document.getElementById('layanan_id').value = res.data.id;
            document.getElementById('layanan_nama').value = res.data.nama_layanan;
            document.getElementById('layanan_harga').value = res.data.harga_per_kg;
            isiPaket(res.paket);
            modalLayanan.show();
        });
}

function hapusData(id) {
    konfirmasiHapus(null, () => {
        const fd = new FormData();
        fd.append(csrfName, csrfHash);
        fetch(`${baseUrl}/layanan/hapus/${id}`, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => { tampilPesan(res.status, res.message); muatTabel(); });
    });
}

document.getElementById('formLayanan').addEventListener('submit', function (e) {
    e.preventDefault();
    const fd = new FormData(this);
    fd.append(csrfName, csrfHash);
    fetch(`${baseUrl}/layanan/simpan`, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'berhasil') { modalLayanan.hide(); muatTabel(); }
            tampilPesan(res.status, res.status === 'berhasil' ? res.message : (res.message + ' ' + Object.values(res.errors ?? {}).join(' ')));
        });
});

muatTabel();
</script>
<?= $this->endSection() ?>
