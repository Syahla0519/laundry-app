<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="card card-soft p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">Daftar Pelanggan</h6>
        <?php if (session()->get('role') === 'pegawai'): ?>
            <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalPelanggan" onclick="bukaTambah()">
                <i class="bi bi-plus-lg"></i> Tambah Pelanggan
            </button>
        <?php else: ?>
            <span class="badge badge-role">Hanya lihat — dicatat oleh pegawai</span>
        <?php endif; ?>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="text-muted small">
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Telepon</th>
                    <th>Alamat</th>
                    <th>Akun Login</th><?php if (session()->get('role') === 'pegawai'): ?><th class="text-end">Aksi</th><?php endif; ?>
                </tr>
            </thead>
            <tbody id="tabelPelanggan"></tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah/Edit Pelanggan -->
<div class="modal fade" id="modalPelanggan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formPelanggan">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold" id="judulModalPelanggan">Tambah Pelanggan</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="pelanggan_id">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Pelanggan</label>
                        <input type="text" class="form-control" name="nama" id="pelanggan_nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nomor Telepon</label>
                        <input type="text" class="form-control" name="telepon" id="pelanggan_telepon" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Alamat</label>
                        <textarea class="form-control" name="alamat" id="pelanggan_alamat" rows="2"></textarea>
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
    const isPegawai = <?= session()->get('role') === 'pegawai' ? 'true' : 'false' ?>;
    const modalPelanggan = new bootstrap.Modal(document.getElementById('modalPelanggan'));

    function bukaTambah() {
        document.getElementById('formPelanggan').reset();
        document.getElementById('pelanggan_id').value = '';
        document.getElementById('judulModalPelanggan').innerText = 'Tambah Pelanggan';
    }

    function muatTabel() {
        fetch(`${baseUrl}/pelanggan/data`)
            .then(r => r.json())
            .then(res => {
                const tbody = document.getElementById('tabelPelanggan');
                tbody.innerHTML = '';
                res.data.forEach((row, i) => {
                    const kolomAksi = isPegawai ? `
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-secondary" onclick="buatAkun(${row.id}, ${row.akun_username ? 'true' : 'false'})" title="${row.akun_username ? 'Reset password akun' : 'Buat akun login pelanggan'}"><i class="bi bi-key"></i></button>
                        <button class="btn btn-sm btn-outline-primary" onclick="bukaEdit(${row.id})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="hapusData(${row.id})"><i class="bi bi-trash"></i></button>
                    </td>` : '';
                    tbody.innerHTML += `
                <tr>
                    <td>${i + 1}</td>
                    <td>${row.nama}</td>
                    <td>${row.telepon}</td>
                    <td>${row.alamat ?? '-'}</td>
                    <td>${row.akun_username ? '<span class="badge badge-role">' + row.akun_username + '</span>' : '<span class="text-muted">Belum ada</span>'}</td>
                    ${kolomAksi}
                </tr>`;
                });
                if (res.data.length === 0) {
                    const colspan = isPegawai ? 6 : 5;
                    tbody.innerHTML = `<tr><td colspan="${colspan}" class="text-center text-muted py-3">Belum ada data pelanggan.</td></tr>`;
                }
            });
    }

    function bukaEdit(id) {
        fetch(`${baseUrl}/pelanggan/edit/${id}`)
            .then(r => r.json())
            .then(res => {
                if (res.status !== 'berhasil') return tampilPesan('gagal', res.message);
                document.getElementById('judulModalPelanggan').innerText = 'Edit Pelanggan';
                document.getElementById('pelanggan_id').value = res.data.id;
                document.getElementById('pelanggan_nama').value = res.data.nama;
                document.getElementById('pelanggan_telepon').value = res.data.telepon;
                document.getElementById('pelanggan_alamat').value = res.data.alamat ?? '';
                modalPelanggan.show();
            });
    }

    function buatAkun(id, sudahAda) {
        const jalankan = () => {
            const fd = new FormData();
            fd.append(csrfName, csrfHash);
            fetch(`${baseUrl}/pelanggan/buat-akun/${id}`, {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(res => {
                    if (res.hash) csrfHash = res.hash;
                    if (res.status !== 'berhasil') return tampilPesan('gagal', res.message);
                    muatTabel();
                    Swal.fire({
                        icon: 'success',
                        title: res.message,
                        html: `<div class="text-start small">Berikan data login ini ke pelanggan (password hanya tampil sekarang):
                           <div class="mt-3 p-3 rounded" style="background:#f4f6f9">
                             Username: <b>${res.username}</b><br>Password: <b>${res.password}</b>
                           </div></div>`,
                        confirmButtonText: 'Sudah dicatat',
                    });
                });
        };
        if (!sudahAda) return jalankan();
        Swal.fire({
            icon: 'question',
            title: 'Reset password akun?',
            text: 'Password lama tidak berlaku lagi dan diganti password baru acak.',
            showCancelButton: true,
            confirmButtonText: 'Ya, reset',
            cancelButtonText: 'Batal',
        }).then(r => {
            if (r.isConfirmed) jalankan();
        });
    }

    function hapusData(id) {
        konfirmasiHapus(null, () => {
            const fd = new FormData();
            fd.append(csrfName, csrfHash);
            fetch(`${baseUrl}/pelanggan/hapus/${id}`, {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(res => {
                    if (res.hash) csrfHash = res.hash;
                    tampilPesan(res.status, res.message);
                    muatTabel();
                });
        });
    }

    document.getElementById('formPelanggan').addEventListener('submit', function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        fd.append(csrfName, csrfHash);
        fetch(`${baseUrl}/pelanggan/simpan`, {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(res => {
                if (res.hash) csrfHash = res.hash;
                if (res.status === 'berhasil') {
                    modalPelanggan.hide();
                    muatTabel();
                }
                tampilPesan(res.status, res.status === 'berhasil' ? res.message : (res.message + ' ' + Object.values(res.errors ?? {}).join(' ')));
            });
    });

    muatTabel();
</script>
<?= $this->endSection() ?>