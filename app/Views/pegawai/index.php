<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="card card-soft p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">Data Pegawai</h6>
        <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalPegawai" onclick="bukaTambah()">
            <i class="bi bi-plus-lg"></i> Tambah Akun
        </button>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="text-muted small">
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Username</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody id="tabelPegawai"></tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah/Edit Pegawai -->
<div class="modal fade" id="modalPegawai" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formPegawai">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold" id="judulModalPegawai">Tambah Akun Pegawai</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="pegawai_id">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama" id="pegawai_nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Username</label>
                        <input type="text" class="form-control" name="username" id="pegawai_username" required>
                    </div>
                    <div class="mb-3" id="wrapGantiPassword" style="display:none;">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="ganti_password" id="pegawai_ganti_password" value="1" onchange="toggleFieldPassword()">
                            <label class="form-check-label small" for="pegawai_ganti_password">Ganti password akun ini</label>
                        </div>
                    </div>
                    <div class="mb-3" id="wrapPassword">
                        <label class="form-label small fw-semibold">Password</label>
                        <input type="password" class="form-control" name="password" id="pegawai_password"
                            autocomplete="new-password" readonly>
                        <div class="form-text" id="hintPassword">Wajib diisi untuk akun baru.</div>
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
    const userIdSaatIni = <?= (int) session()->get('user_id') ?>;
    const modalPegawai = new bootstrap.Modal(document.getElementById('modalPegawai'));

    function toggleFieldPassword() {
        const centang = document.getElementById('pegawai_ganti_password').checked;
        const field = document.getElementById('pegawai_password');
        field.readOnly = !centang;
        field.required = centang;
        if (!centang) field.value = '';
    }

    function bukaTambah() {
        document.getElementById('formPegawai').reset();
        document.getElementById('pegawai_id').value = '';
        document.getElementById('judulModalPegawai').innerText = 'Tambah Akun Pegawai';

        // Akun baru: password langsung wajib diisi, checkbox "ganti password" disembunyikan
        document.getElementById('wrapGantiPassword').style.display = 'none';
        document.getElementById('pegawai_password').readOnly = false;
        document.getElementById('pegawai_password').required = true;
        document.getElementById('hintPassword').innerText = 'Wajib diisi untuk akun baru.';
    }

    function muatTabel() {
        fetch(`${baseUrl}/pegawai/data`)
            .then(r => r.json())
            .then(res => {
                const tbody = document.getElementById('tabelPegawai');
                tbody.innerHTML = '';
                res.data.forEach((row) => {
                    const tombolHapus = (parseInt(row.id) === userIdSaatIni) ?
                        '' :
                        `<button class="btn btn-sm btn-outline-danger" onclick="hapusData(${row.id})"><i class="bi bi-trash"></i></button>`;
                    tbody.innerHTML += `
                <tr>
                    <td>${row.id}</td>
                    <td>${row.nama}</td>
                    <td>${row.username}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary" onclick="bukaEdit(${row.id})"><i class="bi bi-pencil"></i></button>
                        ${tombolHapus}
                    </td>
                </tr>`;
                });
                if (res.data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-3">Belum ada data pegawai.</td></tr>`;
                }
            });
    }

    function bukaEdit(id) {
        fetch(`${baseUrl}/pegawai/edit/${id}`)
            .then(r => r.json())
            .then(res => {
                if (res.status !== 'berhasil') return tampilPesan('gagal', res.message);
                document.getElementById('judulModalPegawai').innerText = 'Edit Akun Pegawai';
                document.getElementById('pegawai_id').value = res.data.id;
                document.getElementById('pegawai_nama').value = res.data.nama;
                document.getElementById('pegawai_username').value = res.data.username;

                // Edit: password dikunci (readonly) sampai centang "Ganti password" dicentang.
                // Ini sengaja supaya browser TIDAK bisa autofill password tersimpan ke field ini.
                document.getElementById('pegawai_ganti_password').checked = false;
                document.getElementById('wrapGantiPassword').style.display = 'block';
                document.getElementById('pegawai_password').value = '';
                document.getElementById('pegawai_password').readOnly = true;
                document.getElementById('pegawai_password').required = false;
                document.getElementById('hintPassword').innerText = 'Centang "Ganti password" dulu kalau mau mengubahnya.';
                modalPegawai.show();
            });
    }

    function hapusData(id) {
        konfirmasiHapus(null, () => {
            const fd = new FormData();
            fd.append(csrfName, csrfHash);
            fetch(`${baseUrl}/pegawai/hapus/${id}`, {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(res => {
                    tampilPesan(res.status, res.message);
                    muatTabel();
                });
        });
    }

    document.getElementById('formPegawai').addEventListener('submit', function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        fd.append(csrfName, csrfHash);
        fetch(`${baseUrl}/pegawai/simpan`, {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'berhasil') {
                    modalPegawai.hide();
                    muatTabel();
                }
                tampilPesan(res.status, res.status === 'berhasil' ? res.message : (res.message + ' ' + Object.values(res.errors ?? {}).join(' ')));
            });
    });

    muatTabel();
</script>
<?= $this->endSection() ?>