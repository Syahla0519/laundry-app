<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #222; }
        h2 { margin-bottom: 0; }
        .sub { color: #666; margin-top: 2px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f0f0f0; }
        tfoot td { font-weight: bold; background: #fafafa; }
    </style>
</head>
<body>
    <h2>Laundry Bersih Cepat</h2>
    <div class="sub">Rekap Pendapatan: <?= esc($mulai) ?> s/d <?= esc($sampai) ?> — dicetak <?= date('d F Y H:i') ?></div>

    <table>
        <thead>
            <tr><th>Tanggal</th><th>Jumlah Transaksi</th><th>Total Pendapatan</th></tr>
        </thead>
        <tbody>
            <?php foreach ($rekap as $r): ?>
            <tr>
                <td><?= esc($r['tanggal_masuk']) ?></td>
                <td><?= (int) $r['jumlah_transaksi'] ?></td>
                <td>Rp <?= number_format($r['total'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($rekap)): ?>
            <tr><td colspan="3">Tidak ada transaksi pada rentang ini.</td></tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr><td colspan="2">Total Pendapatan</td><td>Rp <?= number_format($total, 0, ',', '.') ?></td></tr>
        </tfoot>
    </table>
</body>
</html>
