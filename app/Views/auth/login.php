<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Laundry aja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= base_url('assets/css/deskapp.css') ?>" rel="stylesheet">
    <style>
        /* Halaman login gaya DeskApp: header brand + gambar kiri + kotak login kanan */
        body { background: #ecf0f4; }
        .login-header {
            height: 70px; background: #fff; box-shadow: 0 0 25px rgba(0,0,0,.1);
            display: flex; align-items: center; padding: 0 30px;
        }
        .login-header a { display: flex; align-items: center; gap: 12px; color: #142127; text-decoration: none; font-size: 21px; font-weight: 700; }
        .login-header .brand-mark { width: 38px; height: 38px; border-radius: 10px; background: #1b00ff; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 19px; }
        .login-wrap { min-height: calc(100vh - 70px); display: flex; align-items: center; padding: 30px 0; }
        .login-box { background: #fff; border-radius: 10px; box-shadow: 0 0 28px rgba(0,0,0,.08); padding: 40px 36px; max-width: 420px; width: 100%; margin: 0 auto; }
        .login-box h2 { font-size: 24px; font-weight: 700; color: #1b00ff; margin-bottom: 4px; }
        .input-icon { position: relative; }
        .input-icon i { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); color: #1b00ff; font-size: 18px; }
        .input-icon .form-control { height: 50px; padding-right: 44px; }
    </style>
</head>
<body>
<div class="login-header">
    <a href="<?= site_url('/') ?>"><span class="brand-mark"><i class="bi bi-droplet-half"></i></span> Laundry aja</a>
</div>

<div class="login-wrap">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7 d-none d-lg-block text-center">
                <img src="<?= base_url('assets/img/login-page-img.png') ?>" alt="" class="img-fluid" style="max-height:440px;">
            </div>
            <div class="col-lg-5">
                <div class="login-box">
                    <h2>Login ke Laundry aja</h2>
                    <p class="text-muted mb-4">Sistem Informasi Pengelolaan Laundry</p>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger py-2 small"><?= esc(session()->getFlashdata('error')) ?></div>
                    <?php endif; ?>
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success py-2 small"><?= esc(session()->getFlashdata('success')) ?></div>
                    <?php endif; ?>

                    <form action="<?= site_url('login') ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="input-icon mb-3">
                            <input type="text" name="username" class="form-control" placeholder="Username" required autofocus>
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="input-icon mb-4">
                            <input type="password" name="password" class="form-control" placeholder="**********" required>
                            <i class="bi bi-lock"></i>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Masuk</button>
                    </form>
                    <p class="text-center text-muted small mt-3 mb-0">Hubungi pemilik jika lupa akun.</p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
