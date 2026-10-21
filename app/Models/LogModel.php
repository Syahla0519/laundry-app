<?php

namespace App\Models;

use CodeIgniter\Model;

class LogModel extends Model
{
    protected $table         = 'log_aktivitas';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['user_id', 'nama_user', 'role', 'aksi', 'modul', 'keterangan', 'ip_address'];
    protected $useTimestamps = true;
    protected $updatedField  = ''; // log tidak pernah diubah

    /** Query dasar dengan filter dari halaman Log Aktivitas */
    public function filter(array $f)
    {
        $b = $this->builder();

        if (! empty($f['modul']))   { $b->where('modul', $f['modul']); }
        if (! empty($f['aksi']))    { $b->where('aksi', $f['aksi']); }
        if (! empty($f['dari']))    { $b->where('created_at >=', $f['dari'] . ' 00:00:00'); }
        if (! empty($f['sampai']))  { $b->where('created_at <=', $f['sampai'] . ' 23:59:59'); }
        if (! empty($f['q'])) {
            $b->groupStart()
                ->like('keterangan', $f['q'])
                ->orLike('nama_user', $f['q'])
              ->groupEnd();
        }

        return $b;
    }
}
