<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="card card-soft p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">Daftar Transaksi Cucian</h6>
        <?php if (session()->get('role') === 'pegawai'): ?>
        <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalCucian" onclick="bukaTambah()">
            <i class="bi bi-plus-lg"></i> Terima Cucian Baru
        </button>
        <?php else: ?>
        <span class="badge badge-role">Hanya lihat — dikelola oleh pegawai</span>
        <?php endif; ?>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="text-muted small">
                <tr><th>#</th><th>Pelanggan</th><th>Layanan</th><th>Berat</th><th>Total</th><th>Tgl Masuk</th><th>Status</th><th>Dicatat Oleh</th><th>Diselesaikan Oleh</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody id="tabelCucian"></tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah/Edit Cucian -->
<div class="modal fade" id="modalCucian" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formCucian">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold" id="judulModalCucian">Terima Cucian Baru</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="cucian_id">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pelanggan</label>
                        <select class="form-select" name="pelanggan_id" id="cucian_pelanggan" required>
                            <option value="">-- Pilih Pelanggan --</option>
                            <?php foreach ($pelanggan as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= esc($p['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Jenis Layanan</label>
                        <select class="form-select" name="layanan_id" id="cucian_layanan" required onchange="hitungHarga()">
                            <option value="">-- Pilih Layanan --</option>
                            <?php foreach ($layanan as $l): ?>
                            <option value="<?= $l['id'] ?>" data-harga="<?= $l['harga_per_kg'] ?>"><?= esc($l['nama_layanan']) ?> (Rp <?= number_format($l['harga_per_kg'], 0, ',', '.') ?>/kg)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-semibold">Berat (kg)</label>
                            <input type="number" step="0.1" class="form-control" name="berat_kg" id="cucian_berat" required oninput="hitungHarga()">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-semibold">Tanggal Masuk</label>
                            <input type="date" class="form-control" name="tanggal_masuk" id="cucian_tanggal" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Status Cucian</label>
                        <select class="form-select" name="status" id="cucian_status">
                            <option value="diterima">Diterima</option>
                            <option value="diproses">Diproses</option>
                            <option value="selesai">Selesai</option>
                        </select>
                    </div>
                    <div class="alert alert-primary py-2 small mb-3">
                        Estimasi Total: <strong id="estimasiHarga">Rp 0</strong>
                    </div>
                    <div class="border rounded-3 p-2 small" id="infoPegawai">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted"><i class="bi bi-person-plus"></i> Dicatat oleh</span>
                            <strong id="info_pencatat">-</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted"><i class="bi bi-person-check"></i> Diselesaikan oleh</span>
                            <strong id="info_penyelesai">-</strong>
                        </div>
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

<!-- Modal Detail Cucian -->
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Detail Cucian</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0" id="isiDetail"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ubah Status -->
<div class="modal fade" id="modalStatus" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <form id="formStatus">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Ubah Status Cucian</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="status_id">
                    <select class="form-select" id="status_pilihan">
                        <option value="diterima">Diterima</option>
                        <option value="diproses">Diproses</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Update</button>
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
const isPegawai = <?= session()->get('role') === 'pegawai' ? 'true' : 'false' ?>;
const isPemilik = <?= session()->get('role') === 'pemilik' ? 'true' : 'false' ?>;

function esc(t) {
    return String(t ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}
const modalCucian = new bootstrap.Modal(document.getElementById('modalCucian'));
const modalStatus = new bootstrap.Modal(document.getElementById('modalStatus'));
const modalDetail = new bootstrap.Modal(document.getElementById('modalDetail'));
const namaLogin = <?= json_encode(session()->get('nama') ?? '-') ?>;
let dataCucian = {}; // cache baris tabel (sudah berisi nama pencatat & penyelesai)

/** Isi kotak info "dicatat oleh / diselesaikan oleh" di modal edit */
function isiInfoPegawai(row) {
    document.getElementById('info_pencatat').innerText = row?.nama_pencatat ?? namaLogin;
    updateInfoPenyelesai(row);
}

function updateInfoPenyelesai(row) {
    const el = document.getElementById('info_penyelesai');
    const status = document.getElementById('cucian_status').value;
    if (status === 'selesai') {
        el.innerText = (row?.nama_penyelesai ?? namaLogin) + (row?.nama_penyelesai ? '' : ' (otomatis saat disimpan)');
    } else {
        el.innerText = 'Belum selesai';
    }
}

/** Daftar barang terpakai ("Plastik ×1, Label ×1") dari paket layanan; kecil=true untuk teks mungil di tabel */
function teksBarang(r, kecil) {
    const list = r.barang_terpakai || [];
    if (!list.length) return '';
    const teks = list.map(b => esc(b.nama_barang) + ' ×' + b.jumlah).join(', ');
    return kecil ? `<div class="text-muted" style="font-size:.72rem">Barang: ${teks}</div>` : teks;
}

function lihatDetail(id) {
    const r = dataCucian[id];
    if (!r) return;
    const baris = (label, nilai) => `<div class="d-flex justify-content-between py-2 border-bottom small"><span class="text-muted">${label}</span><span class="fw-semibold text-end">${nilai}</span></div>`;
    document.getElementById('isiDetail').innerHTML =
        baris('Pelanggan', esc(r.nama_pelanggan)) +
        baris('Layanan', esc(r.nama_layanan)) +
        baris('Berat', esc(r.berat_kg) + ' kg') +
        baris('Barang terpakai', teksBarang(r, false) || '<span class="text-muted">-</span>') +
        baris('Total', 'Rp ' + Number(r.total_harga).toLocaleString('id-ID')) +
        baris('Tanggal Masuk', esc(r.tanggal_masuk)) +
        baris('Status', badgeStatus(r.status)) +
        baris('Dicatat oleh', r.nama_pencatat ? esc(r.nama_pencatat) : '<span class="textmuted">Tidak tercatat</span>') +
        baris('Diselesaikan oleh', r.nama_penyelesai ? esc(r.nama_penyelesai) : '<span class="text-muted">' + (r.status === 'selesai' ? 'Tidak tercatat' : 'Belum selesai') + '</span>') +
        baris('Waktu Selesai', r.tanggal_selesai ? esc(r.tanggal_selesai) : '<span class="text-muted">-</span>');
    modalDetail.show();
}

function hitungHarga() {
    const sel = document.getElementById('cucian_layanan');
    const harga = parseFloat(sel.selectedOptions[0]?.dataset.harga ?? 0);
    const berat = parseFloat(document.getElementById('cucian_berat').value ?? 0);
    const total = (harga * berat) || 0;
    document.getElementById('estimasiHarga').innerText = 'Rp ' + total.toLocaleString('id-ID');
}

function bukaTambah() {
    document.getElementById('formCucian').reset();
    document.getElementById('cucian_id').value = '';
    document.getElementById('cucian_tanggal').value = new Date().toISOString().slice(0, 10);
    document.getElementById('cucian_status').value = 'diterima';
    document.getElementById('judulModalCucian').innerText = 'Terima Cucian Baru';
    document.getElementById('estimasiHarga').innerText = 'Rp 0';
    isiInfoPegawai(null);
}

function badgeStatus(status) {
    const label = status.charAt(0).toUpperCase() + status.slice(1);
    return `<span class="badge badge-status-${status}">${label}</span>`;
}

function muatTabel() {
    fetch(`${baseUrl}/cucian/data`)
        .then(r => r.json())
        .then(res => {
            const tbody = document.getElementById('tabelCucian');
            tbody.innerHTML = '';
            dataCucian = {};
            res.data.forEach((row, i) => {
                dataCucian[row.id] = row;
                const terkunci = row.status === 'selesai'; // cucian selesai = final, tidak bisa diedit/dihapus lagi

                const statusCell = (isPegawai && !terkunci)
                    ? `<a href="#" onclick="bukaStatus(${row.id}, '${row.status}'); return false;">${badgeStatus(row.status)}</a>`
                    : badgeStatus(row.status);

                const dicatatOleh = row.nama_pencatat
                    ? esc(row.nama_pencatat)
                    : '<span class="text-muted" title="Data lama / belum tercatat">-</span>';

                let diselesaikanOleh;
                if (row.nama_penyelesai) {
                    diselesaikanOleh = `${esc(row.nama_penyelesai)}<div class="text-muted" style="font-size:.72rem;">${esc(row.tanggal_selesai ?? '')}</div>`;
                } else if (terkunci) {
                    diselesaikanOleh = '<span class="text-muted fst-italic" style="font-size:.8rem;">Tidak tercatat</span>';
                } else {
                    diselesaikanOleh = '<span class="text-muted">-</span>';
                }

                let kolomAksi;
                if (isPegawai) {
                    kolomAksi = terkunci
                        ? `<td class="text-end"><button class="btn btn-sm btn-outline-secondary" onclick="lihatDetail(${row.id})" title="Lihat detail"><i class="bi bi-eye"></i></button> <span class="text-muted" title="Cucian selesai terkunci. Hanya pemilik yang bisa membuka kunci."><i class="bi bi-lock-fill"></i></span></td>`
                        : `<td class="text-end">
                            <button class="btn btn-sm btn-outline-secondary" onclick="lihatDetail(${row.id})" title="Lihat detail"><i class="bi bi-eye"></i></button> 
                            <button class="btn btn-sm btn-outline-primary" onclick="bukaEdit(${row.id})"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-danger" onclick="hapusData(${row.id})"><i class="bi bi-trash"></i></button>
                        </td>`;
                } else {
                    kolomAksi = terkunci
                        ? `<td class="text-end"><button class="btn btn-sm btn-outline-secondary" onclick="lihatDetail(${row.id})" title="Lihat detail"><i class="bi bi-eye"></i></button> <button class="btn btn-sm btn-outline-warning" onclick="bukaKunci(${row.id})" title="Buka kunci cucian ini"><i class="bi bi-unlock-fill"></i> Buka Kunci</button></td>`
                        : `<td class="text-end"><button class="btn btn-sm btn-outline-secondary" onclick="lihatDetail(${row.id})" title="Lihat detail"><i class="bi bi-eye"></i></button> </td>`;
                }

                tbody.innerHTML += `
                <tr>
                    <td>${i + 1}</td>
                    <td>${esc(row.nama_pelanggan)}</td>
                    <td>${esc(row.nama_layanan)}${teksBarang(row, true)}</td>
                    <td>${row.berat_kg} kg</td>
                    <td>Rp ${Number(row.total_harga).toLocaleString('id-ID')}</td>
                    <td>${row.tanggal_masuk}</td>
                    <td>${statusCell}</td>
                    <td>${dicatatOleh}</td>
                    <td>${diselesaikanOleh}</td>
                    ${kolomAksi}
                </tr>`;
            });
            if (res.data.length === 0) {
                const colspan = 10;
                tbody.innerHTML = `<tr><td colspan="${colspan}" class="text-center text-muted py-3">Belum ada transaksi cucian.</td></tr>`;
            }
        });
}

function bukaEdit(id) {
    fetch(`${baseUrl}/cucian/edit/${id}`)
        .then(r => r.json())
        .then(res => {
            if (res.status !== 'berhasil') return tampilPesan('gagal', res.message);
            document.getElementById('judulModalCucian').innerText = 'Edit Cucian';
            document.getElementById('cucian_id').value = res.data.id;
            document.getElementById('cucian_pelanggan').value = res.data.pelanggan_id;
            document.getElementById('cucian_layanan').value = res.data.layanan_id;
            document.getElementById('cucian_berat').value = res.data.berat_kg;
            document.getElementById('cucian_tanggal').value = res.data.tanggal_masuk;
            document.getElementById('cucian_status').value = res.data.status;
            hitungHarga();
            isiInfoPegawai(dataCucian[id]);
            modalCucian.show();
        });
}

function bukaStatus(id, statusSaatIni) {
    document.getElementById('status_id').value = id;
    document.getElementById('status_pilihan').value = statusSaatIni;
    modalStatus.show();
}

function bukaKunci(id) {
    Swal.fire({
        title: 'Buka kunci cucian ini?',
        text: 'Status akan kembali ke "Diproses" dan catatan penyelesai dihapus, sehingga pegawai bisa mengoreksi datanya.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, buka kunci',
        cancelButtonText: 'Batal',
    }).then((result) => {
        if (!result.isConfirmed) return;
        const fd = new FormData();
        fd.append(csrfName, csrfHash);
        fetch(`${baseUrl}/cucian/buka-kunci/${id}`, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => { tampilPesan(res.status, res.message); muatTabel(); });
    });
}

function hapusData(id) {
    konfirmasiHapus(null, () => {
        const fd = new FormData();
        fd.append(csrfName, csrfHash);
        fetch(`${baseUrl}/cucian/hapus/${id}`, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => { tampilPesan(res.status, res.message); muatTabel(); });
    });
}

document.getElementById('cucian_status').addEventListener('change', () => updateInfoPenyelesai(dataCucian[document.getElementById('cucian_id').value]));

document.getElementById('formCucian').addEventListener('submit', function (e) {
    e.preventDefault();
    const fd = new FormData(this);
    fd.append(csrfName, csrfHash);
    fetch(`${baseUrl}/cucian/simpan`, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'berhasil') { modalCucian.hide(); muatTabel(); }
            tampilPesan(res.status, res.status === 'berhasil' ? res.message : (res.message + ' ' + Object.values(res.errors ?? {}).join(' ')));
        });
});

document.getElementById('formStatus').addEventListener('submit', function (e) {
    e.preventDefault();
    const fd = new FormData();
    fd.append(csrfName, csrfHash);
    fd.append('status', document.getElementById('status_pilihan').value);
    const id = document.getElementById('status_id').value;
    fetch(`${baseUrl}/cucian/status/${id}`, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'berhasil') { modalStatus.hide(); muatTabel(); }
            tampilPesan(res.status, res.message);
        });
});

muatTabel();
</script>
<?= $this->endSection() ?>
