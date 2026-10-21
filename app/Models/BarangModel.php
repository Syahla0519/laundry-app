<?php

namespace App\Models;

use CodeIgniter\Model;

class BarangModel extends Model
{
    protected $table         = 'barang';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['nama_barang', 'satuan', 'stok', 'stok_minimum'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'nama_barang'  => 'required|min_length[2]|max_length[100]',
        'satuan'       => 'required|max_length[20]',
        'stok'         => 'required|is_natural',
        'stok_minimum' => 'required|is_natural',
    ];

    protected $validationMessages = [
        'nama_barang'  => ['required' => 'Nama barang wajib diisi'],
        'satuan'       => ['required' => 'Satuan wajib diisi'],
        'stok'         => ['required' => 'Stok wajib diisi', 'is_natural' => 'Stok harus angka 0 atau lebih'],
        'stok_minimum' => ['required' => 'Batas minimum wajib diisi', 'is_natural' => 'Batas minimum harus angka 0 atau lebih'],
    ];

    /** Barang + daftar layanan yang memakainya */
    public function semuaDenganPemakaian(): array
    {
        $barang = $this->orderBy('nama_barang', 'ASC')->findAll();

        $pakai = $this->db->table('layanan_barang lb')
            ->select('lb.barang_id, l.nama_layanan, lb.jumlah')
            ->join('layanan l', 'l.id = lb.layanan_id')
            ->orderBy('l.nama_layanan', 'ASC')
            ->get()->getResultArray();

        $peta = [];
        foreach ($pakai as $p) {
            $peta[$p['barang_id']][] = $p['nama_layanan'] . ' (' . $p['jumlah'] . ')';
        }

        foreach ($barang as &$b) {
            $b['dipakai_di'] = $peta[$b['id']] ?? [];
            $b['menipis']    = (int) $b['stok'] <= (int) $b['stok_minimum'];
        }

        return $barang;
    }

    /** Barang yang stoknya sudah di bawah / sama dengan batas minimum */
    public function stokMenipis(): array
    {
        return $this->where('stok <= stok_minimum', null, false)
            ->orderBy('stok', 'ASC')
            ->findAll();
    }

    /** Paket barang semua layanan: [layanan_id => [[barang_id, nama_barang, satuan, jumlah], ...]] */
    public function paketSemuaLayanan(): array
    {
        $rows = $this->db->table('layanan_barang lb')
            ->select('lb.layanan_id, lb.barang_id, b.nama_barang, b.satuan, b.stok, lb.jumlah')
            ->join('barang b', 'b.id = lb.barang_id')
            ->orderBy('b.nama_barang', 'ASC')
            ->get()->getResultArray();

        $peta = [];
        foreach ($rows as $r) {
            $peta[$r['layanan_id']][] = $r;
        }

        return $peta;
    }

    public function paketLayanan(int $layananId): array
    {
        return $this->db->table('layanan_barang lb')
            ->select('lb.barang_id, b.nama_barang, b.satuan, b.stok, lb.jumlah')
            ->join('barang b', 'b.id = lb.barang_id')
            ->where('lb.layanan_id', $layananId)
            ->orderBy('b.nama_barang', 'ASC')
            ->get()->getResultArray();
    }

    /** Daftar barang yang stoknya tidak cukup untuk 1 cucian dengan layanan ini (kosong = aman) */
    public function kekuranganStok(int $layananId): array
    {
        $kurang = [];
        foreach ($this->paketLayanan($layananId) as $p) {
            if ((int) $p['stok'] < (int) $p['jumlah']) {
                $kurang[] = $p['nama_barang'] . ' (butuh ' . $p['jumlah'] . ', sisa ' . $p['stok'] . ')';
            }
        }

        return $kurang;
    }

    /** Catat barang terpakai (snapshot paket layanan) lalu kurangi stoknya */
    public function pakaiUntukCucian(int $cucianId, int $layananId): void
    {
        foreach ($this->paketLayanan($layananId) as $p) {
            $this->db->table('cucian_barang')->insert([
                'cucian_id' => $cucianId,
                'barang_id' => $p['barang_id'],
                'jumlah'    => $p['jumlah'],
            ]);
            $this->db->query('UPDATE barang SET stok = stok - ? WHERE id = ?', [(int) $p['jumlah'], (int) $p['barang_id']]);
        }
    }

    /** Kembalikan stok dari catatan barang terpakai sebuah cucian, lalu hapus catatannya */
    public function kembalikanDariCucian(int $cucianId): void
    {
        $rows = $this->db->table('cucian_barang')->where('cucian_id', $cucianId)->get()->getResultArray();

        foreach ($rows as $r) {
            $this->db->query('UPDATE barang SET stok = stok + ? WHERE id = ?', [(int) $r['jumlah'], (int) $r['barang_id']]);
        }

        $this->db->table('cucian_barang')->where('cucian_id', $cucianId)->delete();
    }

    /** Ringkasan barang terpakai per cucian: [cucian_id => [['nama_barang','satuan','jumlah'], ...]] */
    public function terpakaiPerCucian(array $cucianIds): array
    {
        if (! $cucianIds) {
            return [];
        }

        $rows = $this->db->table('cucian_barang cb')
            ->select('cb.cucian_id, b.nama_barang, b.satuan, cb.jumlah')
            ->join('barang b', 'b.id = cb.barang_id')
            ->whereIn('cb.cucian_id', $cucianIds)
            ->orderBy('b.nama_barang', 'ASC')
            ->get()->getResultArray();

        $peta = [];
        foreach ($rows as $r) {
            $peta[$r['cucian_id']][] = $r;
        }

        return $peta;
    }
}
